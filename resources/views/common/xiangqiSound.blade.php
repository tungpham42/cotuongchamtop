<script>
    /**
     * XiangqiSound — Advanced AudioContext-based sound engine.
     *
     * Synthesizes high-quality, organic-sounding game cues (wooden impacts,
     * gongs, and bells) via the Web Audio API without relying on external assets.
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

        // Enhanced tone generator with exponential release for natural fading
        function tone(ctx, opts) {
            const {
                freq,
                start,
                duration,
                type = 'sine',
                gain = 0.25,
                freqEnd = null,
                attack = 0.01,
                release = 0.1
            } = opts;

            const osc = ctx.createOscillator();
            const gainNode = ctx.createGain();

            osc.type = type;
            osc.frequency.setValueAtTime(freq, start);
            if (freqEnd !== null) {
                // Exponential ramp sounds more natural to the human ear for pitch drops
                osc.frequency.exponentialRampToValueAtTime(Math.max(freqEnd, 1), start + duration);
            }

            // ADSR Envelope
            gainNode.gain.setValueAtTime(0, start);
            gainNode.gain.linearRampToValueAtTime(gain, start + attack);
            // Smooth exponential decay rather than linear for organic instrument feel
            gainNode.gain.exponentialRampToValueAtTime(0.001, start + duration + release);

            osc.connect(gainNode);
            gainNode.connect(ctx.destination);

            osc.start(start);
            osc.stop(start + duration + release + 0.1);
        }

        // Noise generator for impacts and percussive "clacks"
        function noiseBurst(ctx, opts) {
            const { start, duration, gain = 0.25, filterFreq = 1500, type = 'lowpass' } = opts;

            const bufferSize = Math.max(1, Math.ceil(ctx.sampleRate * duration));
            const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
            const data = buffer.getChannelData(0);
            for (let i = 0; i < bufferSize; i++) {
                data[i] = (Math.random() * 2 - 1);
            }

            const src = ctx.createBufferSource();
            src.buffer = buffer;

            const filter = ctx.createBiquadFilter();
            filter.type = type;
            filter.frequency.value = filterFreq;

            const gainNode = ctx.createGain();
            gainNode.gain.setValueAtTime(gain, start);
            // Snappy decay for percussive hits
            gainNode.gain.exponentialRampToValueAtTime(0.001, start + duration);

            src.connect(filter);
            filter.connect(gainNode);
            gainNode.connect(ctx.destination);

            src.start(start);
            src.stop(start + duration + 0.1);
        }

        const XiangqiSound = {
            /** Standard move: A heavy, resonant wooden "thock". */
            playMove() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                // High-frequency "clack" of the piece
                noiseBurst(ctx, { start: now, duration: 0.03, gain: 0.3, filterFreq: 3000 });
                // Low-frequency resonance of the wooden board
                tone(ctx, { freq: 160, freqEnd: 80, start: now, duration: 0.08, type: 'sine', gain: 0.4, release: 0.05 });
            },

            /** Capture move: Two distinct pieces colliding (sharper clack). */
            playCapture() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                // Sharper, higher frequency impact
                noiseBurst(ctx, { start: now, duration: 0.04, gain: 0.4, filterFreq: 4500 });
                tone(ctx, { freq: 400, freqEnd: 150, start: now, duration: 0.06, type: 'triangle', gain: 0.3, release: 0.05 });
                // Secondary bounce to simulate pieces hitting
                noiseBurst(ctx, { start: now + 0.015, duration: 0.02, gain: 0.2, filterFreq: 2000 });
            },

            /** Game Start: A bright, harmonious ascending chord. */
            playOpening() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                // C major pentatonic ascent (calm, inviting)
                tone(ctx, { freq: 523.25, start: now, duration: 0.15, type: 'sine', gain: 0.2, release: 0.2 });
                tone(ctx, { freq: 659.25, start: now + 0.1, duration: 0.15, type: 'sine', gain: 0.2, release: 0.2 });
                tone(ctx, { freq: 783.99, start: now + 0.2, duration: 0.3, type: 'sine', gain: 0.2, release: 0.4 });
            },

            /** Check: A clear, resonant crystal bell (urgent but not harsh). */
            playCheck() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                // Layered sines create a bell-like timbre, removing the harsh square wave
                tone(ctx, { freq: 880, start: now, duration: 0.1, type: 'sine', gain: 0.3, release: 0.3 });
                tone(ctx, { freq: 1760, start: now, duration: 0.05, type: 'sine', gain: 0.15, release: 0.2 });

                // Double chime for urgency
                tone(ctx, { freq: 880, start: now + 0.15, duration: 0.1, type: 'sine', gain: 0.25, release: 0.3 });
                tone(ctx, { freq: 1760, start: now + 0.15, duration: 0.05, type: 'sine', gain: 0.1, release: 0.2 });
            },

            /** Checkmate: A deep, final gong/taiko drum impact. */
            playCheckmate() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                // Initial strike noise
                noiseBurst(ctx, { start: now, duration: 0.1, gain: 0.4, filterFreq: 1000 });
                // Deep, detuned low frequencies to simulate a large metal gong
                tone(ctx, { freq: 110, freqEnd: 55, start: now, duration: 1.0, type: 'sine', gain: 0.3, release: 0.8 });
                tone(ctx, { freq: 112, freqEnd: 56, start: now, duration: 1.0, type: 'sine', gain: 0.3, release: 0.8 });
                tone(ctx, { freq: 220, freqEnd: 110, start: now, duration: 0.5, type: 'triangle', gain: 0.1, release: 0.5 });
            },

            /** Stalemate / Draw: A soft, neutral resolving hum. */
            playStalemate() {
                if (isMuted()) return;
                const ctx = getContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                // A minor third interval resolving slowly
                tone(ctx, { freq: 329.63, start: now, duration: 0.4, type: 'sine', gain: 0.2, release: 0.4 });
                tone(ctx, { freq: 392.00, start: now, duration: 0.4, type: 'sine', gain: 0.2, release: 0.4 });
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
        // Maintains support for existing blade templates relying on the old audio element calls
        window.nuocCo = { play: function () { XiangqiSound.playMove(); } };
        window.hetTran = { play: function () { XiangqiSound.playGameOver(); } };
        window.playMoveSound = function () { XiangqiSound.playMove(); };
    })();
</script>
