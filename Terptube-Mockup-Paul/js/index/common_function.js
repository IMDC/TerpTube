

//give it a canvas point and it will give back a time
function convertCanvasPointToTime(x, duration)
{
    var canvasPercentage = x / traversalCanvas.width;
    var time = canvasPercentage * duration;
    return time;
}

//give it a time percentage and it will give back a canvas point
function convertTimeToPoint(x, duration)
{
    var timePercentage = x / duration;
    var canvasPoint = timePercentage * traversalCanvas.width;
    return canvasPoint;
}

//round number, num is number you want to round and dec is the number of decimal places
function roundNumber(num, dec) {
    var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
    return result;
}

//generate a random color for the rectangles
function get_random_color() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.round(Math.random() * 15)];
    }

    return color;
}

function clearCanvas(ctx) {
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
}