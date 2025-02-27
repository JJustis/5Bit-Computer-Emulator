<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="5-Bit Computer Emulator with Control Panel Layout">
    <meta name="keywords" content="JavaScript, Emulator, CPU, Memory, Graphics">
    <meta name="author" content="Your Name">
    <title>5-Bit Computer Emulator - Control Panel Layout</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1e1e2f, #3e3e6b, #1e1e2f);
            color: #ffffff;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-size: cover;
        }
        .panel {
            background: rgba(45, 45, 85, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
            padding: 20px;
            width: 900px;
            max-width: 95%;
            display: grid;
            grid-template-columns: 2fr 3fr;
            grid-template-rows: auto auto 1fr;
            gap: 20px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            overflow: hidden;
        }
        h1 {
            grid-column: span 2;
            font-weight: 300;
            text-align: center;
            color: #c0caf5;
            margin-bottom: 20px;
            font-size: 28px;
        }
        .section {
            background: rgba(30, 30, 50, 0.85);
            border-radius: 10px;
            padding: 20px;
            box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .program-editor {
            grid-column: 1 / 2;
            grid-row: 2 / 4;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .program-editor textarea {
            flex-grow: 1;
            margin-bottom: 10px;
            background: #222244;
            color: #ffffff;
            border: 1px solid #555577;
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            box-shadow: inset 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .control-buttons {
            text-align: center;
        }
        .control-buttons button {
            margin: 10px 5px;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            background: linear-gradient(135deg, #7861f9, #60a7d4);
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .control-buttons button:hover {
            background: linear-gradient(135deg, #6651e6, #5096c4);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
        }
        .display-panel {
            grid-column: 2 / 3;
            grid-row: 2 / 3;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .display-panel canvas {
            border: 1px solid #ffffff;
            border-radius: 10px;
            background: radial-gradient(circle at 50% 50%, #1e1e2f, #000);
            margin-top: 10px;
        }
        .info-panel {
            grid-column: 2 / 3;
            grid-row: 3 / 4;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .registers, .status {
            padding: 10px;
            border-radius: 8px;
            background: rgba(30, 30, 50, 0.8);
            font-size: 14px;
            color: #b3c0e6;
            border: 1px solid rgba(255, 255, 255, 0.1);
            flex: 1;
            margin-right: 10px;
        }
        .status {
            text-align: right;
            margin-right: 0;
        }
        #logo {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 48px;
            text-align: center;
            color: #ff9800;
            transition: opacity 1s ease, transform 1s ease;
            opacity: 0;
            transform: translateY(-20px);
        }
        .show-logo {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div style="width:unset !important;" class="panel">
        <h1>5-Bit Computer Emulator</h1>
        <div class="section program-editor">
            <textarea id="programEditor" placeholder="Enter your assembly code here..."></textarea>
            <div class="control-buttons">
                <button onclick="loadProgram()">Load Program</button>
                <button onclick="startEmulator()">Run Program</button>
            </div>
        </div>
        <div class="section display-panel">
            <canvas id="screen" width="640" height="480"></canvas>
        </div>
        <div class="section info-panel">
            <div class="registers" id="registers">Registers: </div>
            <div class="status" id="status">Status: Ready</div>
        </div>
        <div id="logo">STARTUP COMPLETE</div>
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
            const logo = document.getElementById('logo');
            logo.classList.add('show-logo');
            setTimeout(() => {
                logo.classList.remove('show-logo');
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
