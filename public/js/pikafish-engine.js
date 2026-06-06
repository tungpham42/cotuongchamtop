// pikafish-engine.js
class PikafishEngine {
    constructor() {
        this.module = null;
        this.ready = false;
        this.inputQueue = [];
        this.outputBuffer = [];
        this.currentCallback = null;
        this.isThinking = false;
    }

    async init() {
        return new Promise(async (resolve, reject) => {
            try {
                // Initialize the Pikafish module
                this.module = await Module({
                    print: (text) => this.handleOutput(text),
                    printErr: (text) => console.error("Pikafish error:", text),
                    onRuntimeInitialized: () => {
                        this.ready = true;
                        this.sendCommand("uci");
                        resolve();
                    },
                });
            } catch (error) {
                console.error("Failed to initialize Pikafish:", error);
                reject(error);
            }
        });
    }

    handleOutput(text) {
        if (this.currentCallback && this.isThinking) {
            // Check for best move
            if (text.startsWith("bestmove")) {
                const parts = text.split(" ");
                const bestMove = parts[1];
                this.currentCallback(bestMove);
                this.currentCallback = null;
                this.isThinking = false;
            }
        }

        // Store output for debugging
        this.outputBuffer.push(text);
        if (this.outputBuffer.length > 1000) {
            this.outputBuffer.shift();
        }
    }

    sendCommand(command) {
        if (!this.ready) {
            console.warn("Engine not ready yet");
            return;
        }

        // Send each character to stdin
        for (let i = 0; i < command.length; i++) {
            this.module.stdin = () => command.charCodeAt(i);
        }
        // Send newline
        this.module.stdin = () => 10;
    }

    async getBestMove(fen, timeLimit = 1500) {
        if (!this.ready) {
            throw new Error("Engine not initialized");
        }

        if (this.isThinking) {
            throw new Error("Engine is already thinking");
        }

        return new Promise((resolve, reject) => {
            this.currentCallback = resolve;
            this.isThinking = true;

            // Send position and go command
            this.sendCommand(`position fen ${fen}`);
            this.sendCommand(`go movetime ${timeLimit}`);

            // Set timeout
            setTimeout(() => {
                if (this.isThinking) {
                    this.sendCommand("stop");
                    reject(new Error("Engine timeout"));
                    this.isThinking = false;
                    this.currentCallback = null;
                }
            }, timeLimit + 500);
        });
    }

    setOption(name, value) {
        this.sendCommand(`setoption name ${name} value ${value}`);
    }

    quit() {
        if (this.ready) {
            this.sendCommand("quit");
            this.ready = false;
        }
    }
}

// Global instance
let pikafishEngine = null;
