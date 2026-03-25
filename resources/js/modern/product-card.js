export default () => ({
    hover: false,
    addedToCart: false,

    addToCart(productId) {
        // Mock add to cart for now
        this.addedToCart = true;
        setTimeout(() => this.addedToCart = false, 2000);
        console.log('Adding product to cart:', productId);
        
        // This will eventually call the existing AIZ.plugins.addToCart or similar
        if (typeof AIZ !== 'undefined' && AIZ.plugins && AIZ.plugins.addToCart) {
            AIZ.plugins.addToCart(productId);
        }
    }
});
