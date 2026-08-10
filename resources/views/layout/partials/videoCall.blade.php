<!-- resources/views/layout/partials/videoCall.blade.php -->
<div class="card bg-dark text-light my-3 shadow-sm border-secondary" id="video-call-container">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="m-0"><i class="fad fa-video text-danger mr-2"></i>{{ __('Cuộc gọi Video') }}</h6>
        <div>
            <button id="btn-start-call" class="btn btn-sm btn-success pulse-light">
                <i class="fas fa-phone-alt"></i> {{ __('Bắt đầu Gọi') }}
            </button>
            <button id="btn-end-call" class="btn btn-sm btn-danger d-none">
                <i class="fas fa-phone-slash"></i> {{ __('Tắt máy') }}
            </button>
        </div>
    </div>
    <div class="card-body p-2 position-relative bg-black rounded-bottom overflow-hidden" style="min-height: 220px;">
        <!-- Remote Video (Large display) -->
        <!-- Added transition for smooth mirroring effect when signaled by remote peer -->
        <video id="remoteVideo" autoplay playsinline class="w-100 h-100 rounded" style="object-fit: cover; max-height: 350px; background: #111; transition: transform 0.3s ease;"></video>

        <!-- Local Video (Small overlay inset) -->
        <video id="localVideo" autoplay playsinline muted class="position-absolute rounded border border-light shadow"
               style="width: 110px; height: 85px; bottom: 15px; right: 15px; object-fit: cover; background: #222; z-index: 10; transition: transform 0.3s ease;"></video>

        <!-- Media Controls Overlay -->
        <div id="media-controls" class="position-absolute d-none" style="bottom: 15px; left: 15px; z-index: 10;">
            <button id="btn-toggle-audio" class="btn btn-sm btn-dark text-light border-secondary mr-1" data-toggle="tooltip" title="{{ __('Bật/Tắt Mic') }}">
                <i class="fas fa-microphone"></i>
            </button>
            <button id="btn-toggle-video" class="btn btn-sm btn-dark text-light border-secondary mr-1" data-toggle="tooltip" title="{{ __('Bật/Tắt Camera') }}">
                <i class="fas fa-video"></i>
            </button>
            <button id="btn-toggle-mirror" class="btn btn-sm btn-dark text-light border-secondary" data-toggle="tooltip" title="{{ __('Lật Camera') }}">
                <i class="fas fa-arrows-alt-h"></i>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // =========================================================================
    // WEBRTC VIDEO CALL IMPLEMENTATION (COTURN + PUSHER)
    // =========================================================================

    const currentUserId = "{{ auth()->id() ?? session()->getId() }}";
    const videoRoomCode = "{{ $roomCode }}";

    let localStream = null;
    let peerConnection = null;
    let isAudioMuted = false;
    let isVideoStopped = false;
    let isVideoMirrored = false;
    let iceCandidateQueue = [];

    // Coturn Configuration (STUN & TURN)
    const rtcConfig = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' }, // Public STUN fallback
            {
                urls: "{{ config('services.coturn.url') }}",
                username: "{{ config('services.coturn.username') }}",
                credential: "{{ config('services.coturn.credential') }}"
            }
        ]
    };

    // UI Elements
    const $localVideo = document.getElementById('localVideo');
    const $remoteVideo = document.getElementById('remoteVideo');
    const $btnStartCall = document.getElementById('btn-start-call');
    const $btnEndCall = document.getElementById('btn-end-call');
    const $mediaControls = document.getElementById('media-controls');
    const $btnToggleAudio = document.getElementById('btn-toggle-audio');
    const $btnToggleVideo = document.getElementById('btn-toggle-video');
    const $btnToggleMirror = document.getElementById('btn-toggle-mirror');

    // Bulletproof SDP Sanitizer
    function sanitizeSDP(sdp) {
        if (!sdp) return '';
        // Split by any newline variant, trim spaces, remove empty lines, and strictly join with \r\n
        return sdp.split(/\r\n|\r|\n/g)
                  .map(line => line.trim())
                  .filter(line => line.length > 0)
                  .join('\r\n') + '\r\n';
    }

    // 1. Send Signal to API (Sent as strict JSON)
    function sendWebRtcSignal(payload) {
        $.ajax({
            type: "POST",
            url: `{{ url('/api/room') }}/${videoRoomCode}/signal`,
            contentType: "application/json",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: JSON.stringify({
                payload: payload,
                sender_id: currentUserId
            }),
            dataType: 'json'
        }).fail(err => console.error("Error sending WebRTC signal:", err));
    }

    // 2. Initialize Media & PeerConnection
    async function setupLocalStream() {
        try {
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            $localVideo.srcObject = localStream;

            // Set initial mirror state
            $localVideo.style.transform = isVideoMirrored ? 'scaleX(-1)' : 'scaleX(1)';

            $mediaControls.classList.remove('d-none');
            return true;
        } catch (err) {
            console.error("Camera/Microphone access error:", err);
            if (typeof bootbox !== 'undefined') {
                bootbox.alert("{{ __('Không thể truy cập Camera/Microphone của bạn.') }}");
            } else {
                alert("{{ __('Không thể truy cập Camera/Microphone của bạn.') }}");
            }
            return false;
        }
    }

    function createPeerConnection() {
        peerConnection = new RTCPeerConnection(rtcConfig);
        iceCandidateQueue = [];

        // Add local tracks to WebRTC connection
        if (localStream) {
            localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
        }

        // Handle remote media track
        peerConnection.ontrack = (event) => {
            if ($remoteVideo.srcObject !== event.streams[0]) {
                $remoteVideo.srcObject = event.streams[0];

                // If we joined late and the other person is already mirrored, we should let them know our state
                if (isVideoMirrored) {
                    sendWebRtcSignal({ type: 'mirror', isMirrored: isVideoMirrored });
                }
            }
        };

        // Send ICE Candidates through Pusher
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                sendWebRtcSignal({ candidate: event.candidate });
            }
        };

        peerConnection.oniceconnectionstatechange = () => {
            if (peerConnection.iceConnectionState === 'disconnected' || peerConnection.iceConnectionState === 'closed') {
                closeCallUI();
            }
        };
    }

    // Process Queued ICE Candidates safely
    async function processIceQueue() {
        while (iceCandidateQueue.length > 0) {
            const candidate = iceCandidateQueue.shift();
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (err) {
                console.error("Error adding queued ICE candidate:", err);
            }
        }
    }

    // 3. WebRTC Negotiation Handlers
    async function startCall() {
        const streamReady = await setupLocalStream();
        if (!streamReady) return;

        createPeerConnection();

        $btnStartCall.classList.add('d-none');
        $btnEndCall.classList.remove('d-none');

        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        sendWebRtcSignal({ type: 'offer', sdp: offer.sdp });
    }

    async function handleOffer(signal) {
        if (!localStream) {
            const streamReady = await setupLocalStream();
            if (!streamReady) return;
        }

        if (!peerConnection) createPeerConnection();

        $btnStartCall.classList.add('d-none');
        $btnEndCall.classList.remove('d-none');

        const sessionDesc = new RTCSessionDescription({
            type: signal.type,
            sdp: sanitizeSDP(signal.sdp)
        });

        await peerConnection.setRemoteDescription(sessionDesc);
        await processIceQueue();

        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);

        sendWebRtcSignal({ type: 'answer', sdp: answer.sdp });
    }

    async function handleAnswer(signal) {
        if (peerConnection) {
            const sessionDesc = new RTCSessionDescription({
                type: signal.type,
                sdp: sanitizeSDP(signal.sdp)
            });

            await peerConnection.setRemoteDescription(sessionDesc);
            await processIceQueue();
        }
    }

    async function handleCandidate(candidate) {
        if (peerConnection && peerConnection.remoteDescription) {
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (err) {
                console.error("Error adding ICE candidate:", err);
            }
        } else {
            iceCandidateQueue.push(candidate);
        }
    }

    function endCall() {
        sendWebRtcSignal({ type: 'hangup' });
        closeCallUI();
    }

    function closeCallUI() {
        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
        }

        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }

        iceCandidateQueue = [];

        $localVideo.srcObject = null;
        $remoteVideo.srcObject = null;

        // Reset mirror states on both local and remote UI elements on end call
        isVideoMirrored = false;
        $localVideo.style.transform = 'scaleX(1)';
        $remoteVideo.style.transform = 'scaleX(1)';
        $btnToggleMirror.classList.remove('text-info');

        $btnStartCall.classList.remove('d-none');
        $btnEndCall.classList.add('d-none');
        $mediaControls.classList.add('d-none');
    }

    // 4. Listen to Signals via Laravel Echo (Pusher)
    if (typeof Echo !== 'undefined') {
        Echo.channel(`room.${videoRoomCode}`)
            .listen('.webrtc.signal', async (e) => {
                if (String(e.senderId) === String(currentUserId)) return;

                const signal = e.payload;

                try {
                    if (signal.type === 'offer') {
                        await handleOffer(signal);
                    } else if (signal.type === 'answer') {
                        await handleAnswer(signal);
                    } else if (signal.candidate) {
                        await handleCandidate(signal.candidate);
                    } else if (signal.type === 'hangup') {
                        closeCallUI();
                    } else if (signal.type === 'mirror') {
                        // NEW: Handle the mirror command from the remote peer
                        $remoteVideo.style.transform = signal.isMirrored ? 'scaleX(-1)' : 'scaleX(1)';
                    }
                } catch (err) {
                    console.error("WebRTC Error handling signal:", err);
                }
            });
    }

    // 5. Button Listeners
    if ($btnStartCall) $btnStartCall.addEventListener('click', startCall);
    if ($btnEndCall) $btnEndCall.addEventListener('click', endCall);

    if ($btnToggleAudio) {
        $btnToggleAudio.addEventListener('click', () => {
            if (!localStream) return;
            isAudioMuted = !isAudioMuted;
            localStream.getAudioTracks()[0].enabled = !isAudioMuted;
            $btnToggleAudio.innerHTML = isAudioMuted
                ? '<i class="fas fa-microphone-slash text-danger"></i>'
                : '<i class="fas fa-microphone"></i>';
        });
    }

    if ($btnToggleVideo) {
        $btnToggleVideo.addEventListener('click', () => {
            if (!localStream) return;
            isVideoStopped = !isVideoStopped;
            localStream.getVideoTracks()[0].enabled = !isVideoStopped;
            $btnToggleVideo.innerHTML = isVideoStopped
                ? '<i class="fas fa-video-slash text-danger"></i>'
                : '<i class="fas fa-video"></i>';
        });
    }

    // NEW: Mirror Button Listener
    if ($btnToggleMirror) {
        $btnToggleMirror.addEventListener('click', () => {
            if (!localStream) return;
            isVideoMirrored = !isVideoMirrored;

            // Flip the video horizontally locally
            $localVideo.style.transform = isVideoMirrored ? 'scaleX(-1)' : 'scaleX(1)';

            // Send a signal to the remote peer to flip their view of your video
            sendWebRtcSignal({ type: 'mirror', isMirrored: isVideoMirrored });

            // Add visual feedback to the button
            if (isVideoMirrored) {
                $btnToggleMirror.classList.add('text-info');
            } else {
                $btnToggleMirror.classList.remove('text-info');
            }
        });
    }
});
</script>
