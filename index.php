<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="5-Bit Computer Emulator Debugging">
    <meta name="keywords" content="JavaScript, Emulator, CPU, Memory, Graphics">
    <meta name="author" content="Your Name">
    <title>5-Bit Computer Emulator Debugging</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #282c34;
            color: #ffffff;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        canvas {
            border: 1px solid #ffffff;
            margin-top: 10px;
        }
        .container {
            text-align: center;
            max-width: 800px;
        }
        textarea {
            width: 100%;
            height: 100px;
            margin-top: 10px;
            background: #444;
            color: #f0f0f0;
            border: 1px solid #555;
            padding: 10px;
            font-family: 'Courier New', Courier, monospace;
        }
        button {
            margin-top: 10px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #76c7c0;
            color: #ffffff;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #60a7a0;
        }
        .registers, .status {
            margin-top: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>5-Bit Computer Emulator Debugging</h1>
        <textarea id="programEditor" placeholder="Enter your assembly code here..."></textarea>
        <button onclick="loadProgram()">Load Program</button>
        <button onclick="startEmulator()">Run Program</button>
        <canvas id="screen" width="640" height="480"></canvas>
        <div class="registers" id="registers">Registers: </div>
        <div class="status" id="status">Status: Ready</div>
    </div>

    <script>
        const RAM_SIZE = 1024 * 1024 * 1024 / 4;
        const VIDEO_MEMORY_SIZE = 64 * 1024 * 1024 / 4;
        const REGISTER_COUNT = 16;
        let ram = new Uint32Array(RAM_SIZE);
        let videoMemory = new Uint32Array(VIDEO_MEMORY_SIZE);
        let registers = new Uint8Array(REGISTER_COUNT);
        let pc = 0;
        let sp = RAM_SIZE - 1;
        let running = false;

        const INSTRUCTION_SET = {
            0: 'NOP',
            1: 'MOV',
            2: 'ADD',
            3: 'SUB',
            4: 'MUL',
            5: 'DIV',
            6: 'SHL',
            7: 'SHR',
            8: 'AND',
            9: 'OR',
            10: 'XOR',
            11: 'INC',
            12: 'DEC',
            13: 'JMP',
            14: 'CMP',
            15: 'JEQ',
            16: 'JNE',
            17: 'JLT',
            18: 'JGT',
            19: 'LOAD',
            20: 'STORE',
            21: 'PUSH',
            22: 'POP',
            23: 'CALL',
            24: 'RET',
            25: 'DRAW',
            26: 'HLT'
        };

        let comparisonResult = 0;

        function loadProgram() {
            const program = document.getElementById('programEditor').value.split('\n');
            let address = 0;
            program.forEach((line, index) => {
                const [opcode, dest, src] = line.trim().split(' ');
                const opCodeValue = Object.keys(INSTRUCTION_SET).find(key => INSTRUCTION_SET[key] === opcode);
                if (opCodeValue !== undefined) {
                    const instruction = assembleInstruction(parseInt(opCodeValue), dest, src);
                    console.log(`Line ${index + 1}: Loaded ${opcode} with opcode ${opCodeValue} to RAM at address ${address}`);
                    ram[address++] = instruction;
                } else {
                    console.log(`Unknown instruction at line ${index + 1}: ${line}`);
                }
            });
            pc = 0;
            updateStatus('Program loaded into RAM.');
        }

        function assembleInstruction(opCodeValue, dest, src) {
            console.log(`Assembling: Opcode: ${opCodeValue}, Dest: ${dest}, Src: ${src}`);
            const destValue = dest ? parseInt(dest.replace('R', ''), 10) || 0 : 0;
            const srcValue = src ? parseInt(src, 10) || 0 : 0;
            return (opCodeValue << 27) | (destValue << 22) | (srcValue & 0xFFFF);
        }

        function cpuStep() {
            if (!running) return;
            let instruction = ram[pc];
            let opcode = (instruction >> 27) & 0x1F;
            let dest = (instruction >> 22) & 0x1F;
            let value = instruction & 0xFFFF;

            console.log(`Executing: Opcode: ${opcode}, Dest: ${dest}, Value: ${value}`);

                       switch (opcode) {
                case 0: break; // NOP
                case 1: registers[dest] = value; break;  // MOV
                case 2: registers[dest] += registers[value]; break;  // ADD
                case 3: registers[dest] -= registers[value]; break;  // SUB
                case 4: registers[dest] *= registers[value]; break;  // MUL
                case 5: registers[dest] = Math.floor(registers[dest] / registers[value]); break;  // DIV
                case 6: registers[dest] <<= value; break;  // SHL
                case 7: registers[dest] >>= value; break;  // SHR
                case 8: registers[dest] &= registers[value]; break;  // AND
                case 9: registers[dest] |= registers[value]; break;  // OR
                case 10: registers[dest] ^= registers[value]; break;  // XOR
                case 11: registers[dest] += 1; break;  // INC
                case 12: registers[dest] -= 1; break;  // DEC
                case 13: pc = value; return;  // JMP
                case 14: comparisonResult = registers[dest] - value; break;  // CMP
                case 15: if (comparisonResult === 0) { pc = value; return; } break;  // JEQ
                case 16: if (comparisonResult !== 0) { pc = value; return; } break;  // JNE
                case 17: if (comparisonResult < 0) { pc = value; return; } break;  // JLT
                case 18: if (comparisonResult > 0) { pc = value; return; } break;  // JGT
                case 19: registers[dest] = ram[value]; break;  // LOAD
                case 20: ram[value] = registers[dest]; break;  // STORE
                case 21: ram[--sp] = registers[dest]; break;  // PUSH
                case 22: registers[dest] = ram[sp++]; break;  // POP
                case 23: ram[--sp] = pc; pc = value; return;  // CALL
                case 24: pc = ram[sp++]; return;  // RET
                case 25: drawPixel(registers[dest], value); break;  // DRAW
                case 26: running = false; updateStatus('Emulator halted.'); return;  // HLT
                default: console.log(`Unknown opcode: ${opcode}`); break;
            }
            pc += 1;
            updateRegisters();
        }

        function startEmulator() {
            running = true;
            updateStatus('Emulator running...');
            mainLoop();
        }

        function mainLoop() {
            if (running) {
                cpuStep();
                requestAnimationFrame(mainLoop);
            }
        }

        function updateRegisters() {
            document.getElementById('registers').innerText = `Registers: ${Array.from(registers).map((reg, i) => `R${i}: ${reg}`).join(', ')}`;
        }

        function updateStatus(message) {
            document.getElementById('status').innerText = `Status: ${message}`;
        }

        // Draw a pixel on the canvas
        function drawPixel(x, y) {
            if (x < 640 && y < 480) {
                let index = y * 640 + x;
                videoMemory[index] = 0xFFFFFF; // Set pixel color to white
                console.log(`Drawing pixel at (${x}, ${y})`);
            }
        }

        // Render the video memory to the canvas
        function renderVideoMemory() {
            const canvas = document.getElementById('screen');
            const ctx = canvas.getContext('2d');
            const imageData = ctx.createImageData(canvas.width, canvas.height);

            for (let i = 0; i < canvas.width * canvas.height; i++) {
                let color = videoMemory[i];
                let index = i * 4;
                imageData.data[index] = (color >> 16) & 0xFF;     // Red
                imageData.data[index + 1] = (color >> 8) & 0xFF;  // Green
                imageData.data[index + 2] = color & 0xFF;         // Blue
                imageData.data[index + 3] = 255;                  // Alpha
            }
            ctx.putImageData(imageData, 0, 0);
        }

        function startEmulator() {
            running = true;
            updateStatus('Emulator running...');
            startupSequence();
        }

        function startupSequence() {
            const canvas = document.getElementById('screen');
            const ctx = canvas.getContext('2d');
            
            ctx.fillStyle = "black";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Start the star-like animation
            let animationSteps = 0;
            const starInterval = setInterval(() => {
                if (animationSteps < 20) {
                    ctx.strokeStyle = `rgb(${255 - animationSteps * 10}, ${animationSteps * 10}, ${255 - animationSteps * 10})`;
                    ctx.beginPath();
                    ctx.moveTo(canvas.width / 2, canvas.height / 2);
                    ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
                    ctx.stroke();
                    animationSteps++;
                } else {
                    clearInterval(starInterval);
                    showLogo();
                }
            }, 100);
        }

        function showLogo() {
            document.getElementById('logo').style.display = 'block';
            setTimeout(() => {
                document.getElementById('logo').style.display = 'none';
                renderRetroScreen();
            }, 2000);
        }

        function renderRetroScreen() {
            const canvas = document.getElementById('screen');
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = "#FF9800"; // Retro orange screen color
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        document.getElementById('programEditor').value = `
            MOV R0, 0
            MOV R1, 240
            DRAW_LINE:
            DRAW R0, R1
            INC R0
            CMP R0, 640
            JLT DRAW_LINE
            HLT
        `;
        loadProgram();
    </script>
</body>
</html>
