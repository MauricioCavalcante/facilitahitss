document.addEventListener('DOMContentLoaded', () => {
    const groups = [
        { pdfInputId: 'pdfUploadGroup1', labelIndexes: [0, 1], label: 'Anexo Base R1' },
        { pdfInputId: 'pdfUploadGroup2', labelIndexes: [2, 3, 4], label: 'R1 - Quantidade geral por tipo' },
        { pdfInputId: 'pdfUploadGroup3', labelIndexes: [5], label: 'Anexo Base R3' },
    ];

    let pdfDocuments = {};
    let canvases = {};

    groups.forEach(group => {
        const input = document.getElementById(group.pdfInputId);

        input.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const fileReader = new FileReader();
            fileReader.onload = async function () {
                const typedarray = new Uint8Array(this.result);
                const pdf = await pdfjsLib.getDocument({ data: typedarray }).promise;
                pdfDocuments[group.pdfInputId] = pdf;
            };
            fileReader.readAsArrayBuffer(file);
        });

        group.labelIndexes.forEach(index => {
            const btn = document.getElementById(`cropLabel${index}`);
            btn.addEventListener('click', async () => {
                // Já tem PDF carregado do input
                if (pdfDocuments[group.pdfInputId]) {
                    renderPdf(pdfDocuments[group.pdfInputId], index);
                    return;
                }

                // Tenta carregar do banco (via base64 inline)
                const base64 = pdfBase64FromDB[group.label]?.base64;
                if (!base64) {
                    alert("Primeiro anexe o arquivo");
                    return;
                }

                const response = await fetch(base64);
                const blob = await response.blob();
                const arrayBuffer = await blob.arrayBuffer();
                const typedarray = new Uint8Array(arrayBuffer);
                const pdf = await pdfjsLib.getDocument({ data: typedarray }).promise;

                pdfDocuments[group.pdfInputId] = pdf;
                renderPdf(pdf, index);
            });
        });
    });

    function renderPdf(pdf, index) {
        const viewerDiv = document.getElementById(`pdfViewer${index}`);
        viewerDiv.innerHTML = '';

        if (index === 5) {
            Promise.all([pdf.getPage(3), pdf.getPage(4)]).then(async ([page3, page4]) => {
                const viewport3 = page3.getViewport({ scale: 1.5 });
                const viewport4 = page4.getViewport({ scale: 1.5 });

                const canvas3 = document.createElement('canvas');
                canvas3.width = viewport3.width;
                canvas3.height = viewport3.height;
                const context3 = canvas3.getContext('2d');
                await page3.render({ canvasContext: context3, viewport: viewport3 }).promise;

                const canvas4 = document.createElement('canvas');
                canvas4.width = viewport4.width;
                canvas4.height = viewport4.height;
                const context4 = canvas4.getContext('2d');
                await page4.render({ canvasContext: context4, viewport: viewport4 }).promise;

                viewerDiv.appendChild(canvas3);
                viewerDiv.appendChild(document.createElement('hr'));
                viewerDiv.appendChild(canvas4);

                canvases[`${index}_3`] = canvas3;
                canvases[`${index}_4`] = canvas4;

                enableDualCrop(canvas3, canvas4, index);
            });
        } else {
            pdf.getPage(1).then(async page => {
                const viewport = page.getViewport({ scale: 1.5 });

                const canvas = document.createElement('canvas');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                viewerDiv.appendChild(canvas);
                canvases[index] = canvas;

                const context = canvas.getContext('2d');
                await page.render({ canvasContext: context, viewport }).promise;

                enableCrop(canvas, index);
            });
        }
    }

    // Copiado de seu script original:
    document.querySelectorAll('.toggle-viewer').forEach(button => {
        button.addEventListener('click', () => {
            const index = button.getAttribute('data-index');
            const viewer = document.getElementById(`pdfViewer${index}`);
            if (!viewer) return;

            const icon = button.querySelector('i');
            const isHidden = viewer.style.display === 'none';
            viewer.style.display = isHidden ? 'block' : 'none';

            icon.classList.toggle('bi-list', !isHidden);
            icon.classList.toggle('bi-x-lg', isHidden);
        });
    });

    // Coloque aqui `enableCrop` e `enableDualCrop` iguais aos do create.js
    // Eles funcionam do mesmo jeito

    function enableCrop(canvas, index) {
        let isDown = false;
        let startX, startY, endX, endY;
        const ctx = canvas.getContext('2d');
        let originalImage = null;
    
        canvas.onmousedown = (e) => {
            isDown = true;
            const rect = canvas.getBoundingClientRect();
            startX = e.clientX - rect.left;
            startY = e.clientY - rect.top;
    
            // Salva imagem original no momento do clique
            originalImage = ctx.getImageData(0, 0, canvas.width, canvas.height);
        };
    
        canvas.onmousemove = (e) => {
            if (!isDown || !originalImage) return;
            const rect = canvas.getBoundingClientRect();
            endX = e.clientX - rect.left;
            endY = e.clientY - rect.top;
    
            ctx.putImageData(originalImage, 0, 0);
            ctx.beginPath();
            ctx.rect(startX, startY, endX - startX, endY - startY);
            ctx.lineWidth = 2;
            ctx.strokeStyle = 'red';
            ctx.stroke();
        };
    
        canvas.onmouseup = () => {
            isDown = false;
    
            const x = Math.min(startX, endX);
            const y = Math.min(startY, endY);
            const width = Math.abs(endX - startX);
            const height = Math.abs(endY - startY);
    
            const croppedCanvas = document.createElement('canvas');
            croppedCanvas.width = width;
            croppedCanvas.height = height;
    
            const croppedCtx = croppedCanvas.getContext('2d');
            croppedCtx.putImageData(originalImage, -x, -y);
    
            const dataURL = croppedCanvas.toDataURL('image/png');
            document.getElementById(`croppedImage${index}`).value = dataURL;
            document.getElementById(`preview${index}`).src = dataURL;
    
            alert("Imagem recortada com sucesso!");
        };
    }
    
    function enableDualCrop(canvas1, canvas2, index) {
        let croppedImage1 = null;
        let croppedImage2 = null;
    
        const cropCanvas = (canvas, callback) => {
            let isDown = false;
            let startX, startY, endX, endY;
            const ctx = canvas.getContext('2d');
            let originalImage = null;
    
            canvas.onmousedown = (e) => {
                isDown = true;
                const rect = canvas.getBoundingClientRect();
                startX = e.clientX - rect.left;
                startY = e.clientY - rect.top;
    
                originalImage = ctx.getImageData(0, 0, canvas.width, canvas.height);
            };
    
            canvas.onmousemove = (e) => {
                if (!isDown || !originalImage) return;
                const rect = canvas.getBoundingClientRect();
                endX = e.clientX - rect.left;
                endY = e.clientY - rect.top;
    
                ctx.putImageData(originalImage, 0, 0);
                ctx.beginPath();
                ctx.rect(startX, startY, endX - startX, endY - startY);
                ctx.lineWidth = 2;
                ctx.strokeStyle = 'red';
                ctx.stroke();
            };
    
            canvas.onmouseup = () => {
                isDown = false;
    
                const x = Math.min(startX, endX);
                const y = Math.min(startY, endY);
                const width = Math.abs(endX - startX);
                const height = Math.abs(endY - startY);
    
                const croppedCanvas = document.createElement('canvas');
                croppedCanvas.width = width;
                croppedCanvas.height = height;
    
                const croppedCtx = croppedCanvas.getContext('2d');
                croppedCtx.putImageData(originalImage, -x, -y);
    
                callback(croppedCanvas);
            };
        };
    
        cropCanvas(canvas1, (croppedCanvas1) => {
            croppedImage1 = croppedCanvas1;
            alert("Primeira parte (página 3) recortada! Agora recorte a parte da página 4.");
    
            cropCanvas(canvas2, (croppedCanvas2) => {
                croppedImage2 = croppedCanvas2;
    
                const totalWidth = Math.max(croppedImage1.width, croppedImage2.width);
                const totalHeight = croppedImage1.height + croppedImage2.height;
    
                const mergedCanvas = document.createElement('canvas');
                mergedCanvas.width = totalWidth;
                mergedCanvas.height = totalHeight;
    
                const ctx = mergedCanvas.getContext('2d');
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, totalWidth, totalHeight);
                ctx.drawImage(croppedImage1, 0, 0);
                ctx.drawImage(croppedImage2, 0, croppedImage1.height);
    
                const dataURL = mergedCanvas.toDataURL('image/png');
                document.getElementById(`croppedImage${index}`).value = dataURL;
                document.getElementById(`preview${index}`).src = dataURL;
    
                alert("Imagem final mesclada e salva com sucesso!");
            });
        });
    }
});
