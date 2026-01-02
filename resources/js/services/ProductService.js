
export const mockProducts = [
    {
        id: 1,
        name: 'Premium Headphones',
        price: '299.00',
        regular_price: '350.00',
        sale_price: '299.00',
        on_sale: true,
        currency: '$',
        sku: 'AUD-001',
        stock_status: 'instock',
        images: [
            'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&q=80',
            'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=500&q=80'
        ],
        categories: [{ id: 10, name: 'Electronics' }, { id: 11, name: 'Audio' }],
        rating: 4.5,
        review_count: 12,
        short_description: 'High-fidelity audio with noise cancellation.',
        description: 'Experience sound like never before with our premium headphones. Features include active noise cancellation, 30-hour battery life, and plush comfort for long listening sessions.'
    },
    {
        id: 2,
        name: 'Ergonomic Chair',
        price: '189.00',
        regular_price: '189.00',
        sale_price: '',
        on_sale: false,
        currency: '$',
        sku: 'FUR-002',
        stock_status: 'instock',
        images: [
            'https://images.unsplash.com/photo-1592078615290-033ee584e267?w=500&q=80'
        ],
        categories: [{ id: 20, name: 'Furniture' }, { id: 21, name: 'Office' }],
        rating: 5.0,
        review_count: 8,
        short_description: 'Comfort for your workspace.',
        description: 'Designed for posture and support, this chair is perfect for remote work.'
    },
    {
        id: 3,
        name: 'Smart Watch',
        price: '149.00',
        regular_price: '199.00',
        sale_price: '149.00',
        on_sale: true,
        currency: '$',
        sku: 'TEC-003',
        stock_status: 'outofstock',
        images: [
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&q=80'
        ],
        categories: [{ id: 10, name: 'Electronics' }],
        rating: 4.0,
        review_count: 25,
        short_description: 'Track your fitness goals.',
        description: 'Stay connected and healthy with this feature-packed smart watch.'
    },
    {
        id: 4,
        name: 'Minimalist Pot',
        price: '25.00',
        regular_price: '25.00',
        sale_price: '',
        on_sale: false,
        currency: '$',
        sku: 'DEC-004',
        stock_status: 'instock',
        images: [
            'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=500&q=80'
        ],
        categories: [{ id: 30, name: 'Decor' }],
        rating: 4.8,
        review_count: 5,
        short_description: 'Add green to your room.',
        description: 'Ceramic pot with a matte finish. Perfect for succulents.'
    }
];

export const productService = {
    getProducts(params = {}) {
        let results = [...mockProducts];
        if (params.limit) {
            results = results.slice(0, params.limit);
        }
        return Promise.resolve(results);
    },
    getProduct(id) {
        return Promise.resolve(mockProducts.find(p => p.id === parseInt(id)) || mockProducts[0]);
    }
};
