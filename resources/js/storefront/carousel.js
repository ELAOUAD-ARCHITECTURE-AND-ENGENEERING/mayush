export function initializeCarousel(root = document) {
    if (window.AIZ?.plugins?.slickCarousel) {
        window.AIZ.plugins.slickCarousel(root);
    }
}
