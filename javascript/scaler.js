function getZoomLevel() {
    return Math.round((window.outerWidth / window.innerWidth) * 100);
}

function updateScale() {
    const zoomLevel = getZoomLevel();
    const scaleFactor = zoomLevel / 100;

    const container = document.querySelector('.container');
    container.style.transform = `translateY(-50%) scale(${scaleFactor})`;
}

window.addEventListener('load', () => {
    updateScale(); 
});

window.addEventListener('resize', updateScale);