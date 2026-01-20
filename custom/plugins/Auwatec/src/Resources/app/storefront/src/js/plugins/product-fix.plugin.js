import Plugin from 'src/plugin-system/plugin.class';

export default class ProductFixPlugin extends Plugin {
    
    init() {
        this.fixProblematicProduct();
        this.removeInlineWidthConstraints();
    }

    fixProblematicProduct() {
        // Check if we're on a product detail page
        const productDescription = document.querySelector('.product-detail-description-text');
        
        if (productDescription) {
            // Look for the specific problematic font tags
            const problematicFonts = productDescription.querySelectorAll('font[face*="Open Sans"], font[face*="sans-serif"]');
            
            if (problematicFonts.length > 0) {
                console.log('Found problematic product, applying fixes...');
                
                // Fix 1: Clean up the font tags
                problematicFonts.forEach(font => {
                    // Remove the font tag but keep the content
                    const content = font.innerHTML;
                    const textNode = document.createTextNode(font.textContent || font.innerText);
                    font.parentNode.replaceChild(textNode, font);
                });
                
                console.log('Product fixes applied successfully');
            }
        }
    }
    
    removeInlineWidthConstraints() {
        // Remove inline width constraints from product descriptions
        const descriptionContainer = document.querySelector('.cms-block-product-description-reviews');
        if (descriptionContainer) {
            const allElements = descriptionContainer.querySelectorAll('*');
            allElements.forEach(el => {
                el.style.maxWidth = '';
                el.style.width = '';
                // Also remove any problematic inline styles
                if (el.style.minWidth) el.style.minWidth = '';
                if (el.style.overflow) el.style.overflow = '';
            });
            console.log('Removed inline width constraints from description');
        }
    }
}