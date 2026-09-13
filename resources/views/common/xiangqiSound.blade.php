<script>
    /**
     * XiangqiSound — AudioContext-based sound engine.
     *
     * Replaces the old <audio id="nuoc-co">/<audio id="het-tran"> mp3/wav
     * playback with sounds synthesized on the fly via the Web Audio API.
     * No network requests, no autoplay-policy issues with <audio> tags,
     * and each game event gets its own distinct sound instead of every
     * move/game-over sharing one clip.
     *
     * Backward compatibility:
     *   - window.nuocCo.play()      -> XiangqiSound.playMove()
     *   - window.hetTran.play()     -> XiangqiSound.playGameOver()
     *   - window.playMoveSound()    -> XiangqiSound.playMove()
     * so existing blade templates that already call these keep working
     * unmodified. The hidden #nuoc-co / #het-tran elements are kept (with
     * no <source>) purely so the existing volume mute toggle — which
     * likely selects on `audio` elements — still has something to target;
     * XiangqiSound reads their `.muted` property before playing anything.
     */
    (function () {
        'use strict';

        let audioCtx = null;

        function getContext() {
            if (!audioCtx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (!AudioContextClass) return null;
                audioCtx = new AudioContextClass();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            return audioCtx;
        }

        function isMuted() {
            const el = document.getElementById('nuoc-co');
            return !!(el && el.muted);
        }

        function tone(ctx, opts) {
            const {
                freq,
                start,
                duration,
                type = 'sine',
                gain = 0.25,
                freqEnd = null,
                attack = 0.005,
                release = 0.06
            } = opts;

            const osc = ctx.createOscillator();
            const gainNode = ctx.createGain();

            osc.type = type;
            osc.frequency.setValueAtTime(freq, start);
            if (freqEnd !== null) {
                osc.frequency.exponentialRampToValueAtTime(Math.max(freqEnd, 1), start + duration);
            }

            gainNode.gain.setValueAtTime(0, start);
            gainNode.gain.linearRampToValueAtTime(gain, start + attack);
            gainNode.gain.linearRampToValueAtTime(0, start + duration + release);

            osc.connect(gainNode);
            gainNode.connect(ctx.destination);

            osc.start(start);
            osc.stop(start + duration + release + 0.02);
        }

        function noiseBurst(ctx, opts) {
            const { start, duration, gain = 0.25, filterFreq = 1500 } = opts;

            const bufferSize = Math.max(1, Math.ceil(ctx.sampleRate * duration));
            const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
            const data = buffer.getChannelData(0);
            for (let i = 0; i < bufferSize; i++) {
                data[i] = (Math.random() * 2 - 1) * (1 - i / bufferSize);
            }

            const src = ctx.createBufferSource();
            src.buffer = buffer;

            const filter = ctx.createBiquadFilter();
            filter.type = 'lowpass';
            filter.frequency.value = filterFreq;

            const gainNode = ctx.createGain();
            gainNode.gain.setValueAtTime(gain, start);
            gainNode.gain.exponentialRampToValueAtTime(0.001, start + duration);

            src.connect(filter);
            filter.connect(gainNode);
            gainNode.connect(ctx.destination);

            src.start(start);
            src.stop(start + duration + 0.02);
        }

        const XiangqiSound = {
            /** Standard move: a short wooden "click" (filtered noise + soft thud). */
            playMove() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;
                noiseBurst(ctx, { start: now, duration: 0.045, gain: 0.35, filterFreq: 2000 });
                tone(ctx, { freq: 190, start: now, duration: 0.06, type: 'sine', gain: 0.22, freqEnd: 90 });
            },

            /** First move of a game: brighter two-note chime marking the start. */
            playOpening() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;
                noiseBurst(ctx, { start: now, duration: 0.045, gain: 0.25, filterFreq: 2000 });
                tone(ctx, { freq: 523.25, start: now, duration: 0.11, type: 'triangle', gain: 0.2 });
                tone(ctx, { freq: 783.99, start: now + 0.1, duration: 0.16, type: 'triangle', gain: 0.2 });
            },

            /** A move that puts a king in check: sharp, urgent double ping. */
            playCheck() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;
                tone(ctx, { freq: 987.77, start: now, duration: 0.08, type: 'square', gain: 0.16 });
                tone(ctx, { freq: 987.77, start: now + 0.11, duration: 0.08, type: 'square', gain: 0.16 });
            },

            /** Checkmate: dramatic descending tone + low gong thud. */
            playCheckmate() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;
                noiseBurst(ctx, { start: now, duration: 0.15, gain: 0.25, filterFreq: 700 });
                tone(ctx, { freq: 440, start: now, duration: 0.28, type: 'sawtooth', gain: 0.18, freqEnd: 110 });
                tone(ctx, { freq: 220, start: now + 0.05, duration: 0.5, type: 'sine', gain: 0.22, freqEnd: 55 });
            },

            /** Stalemate / draw: neutral, flat two-tone — no winner. */
            playStalemate() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;
                tone(ctx, { freq: 392, start: now, duration: 0.2, type: 'sine', gain: 0.18 });
                tone(ctx, { freq: 392, start: now + 0.24, duration: 0.2, type: 'sine', gain: 0.18 });
            },

            /**
             * Generic game-over dispatcher, kept for old call sites that
             * only knew "game over" without distinguishing why.
             * @param {'checkmate'|'stalemate'|'draw'|string|undefined} reason
             */
            playGameOver(reason) {
                if (reason === 'stalemate' || reason === 'draw') {
                    return this.playStalemate();
                }
                return this.playCheckmate();
            }
        };

        window.XiangqiSound = XiangqiSound;

        // --- Backward-compatible shims -------------------------------------------------
        // Existing templates call nuocCo.play() / hetTran.play() / playMoveSound().
        window.nuocCo = { play: function () { XiangqiSound.playMove(); } };
        window.hetTran = { play: function () { XiangqiSound.playGameOver(); } };
        window.playMoveSound = function () { XiangqiSound.playMove(); };
    })();
</script>
