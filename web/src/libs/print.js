import html2canvas from 'html2canvas';

let printForm = function(title, imgData, width) {

    let printWin = window.open();
    printWin.document.write(`<html>`)
    printWin.document.write(`<head><title>${title}</title></head>`)
    printWin.document.write(`<body>`)
    printWin.document.write(`<div style="text-align: center;background:#fff;"><img style="background:#fff;" width='${width}' src='${imgData}'/></div>`);
    printWin.document.write(`</body></html>`)

    setTimeout(function(){
        printWin.print();
        printWin.close();
    }, 500)

}

let printImage = function(domIdName, title) {
    let domId = document.getElementById(domIdName);
    let width = domId.clientWidth;

    html2canvas(domId, {
        scale: 1.6,
        backgroundColor: null,
    }).then(canvas => {
        let imgData = canvas.toDataURL("image/png")
        printForm(title, imgData, width)
    })

}

export default {printImage};