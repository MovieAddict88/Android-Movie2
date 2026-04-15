export interface FoodItem {
  id: string;
  name: string;
  description: string;
  price: number;
  category: string;
  image: string;
  rating: number;
  prepTime: string;
}

export const CATEGORIES = ["All", "Burgers", "Pizza", "Sushi", "Salads", "Desserts"];

export const MENU_ITEMS: FoodItem[] = [
  {
    id: "1",
    name: "Classic Vibe Burger",
    description: "Juicy beef patty with secret vibe sauce, lettuce, and melted cheddar.",
    price: 12.99,
    category: "Burgers",
    image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=500&auto=format&fit=crop",
    rating: 4.8,
    prepTime: "15-20 min"
  },
  {
    id: "2",
    name: "Truffle Mushroom Pizza",
    description: "Hand-tossed dough with truffle oil, wild mushrooms, and fresh mozzarella.",
    price: 18.50,
    category: "Pizza",
    image: "https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=500&auto=format&fit=crop",
    rating: 4.9,
    prepTime: "20-25 min"
  },
  {
    id: "3",
    name: "Salmon Zen Roll",
    description: "Fresh salmon, avocado, and cucumber with spicy mayo and crispy onions.",
    price: 15.00,
    category: "Sushi",
    image: "https://images.unsplash.com/photo-1579871494447-9811cf80d66c?q=80&w=500&auto=format&fit=crop",
    rating: 4.7,
    prepTime: "10-15 min"
  },
  {
    id: "4",
    name: "Quinoa Power Bowl",
    description: "Organic quinoa, roasted chickpeas, kale, and lemon-tahini dressing.",
    price: 14.25,
    category: "Salads",
    image: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=500&auto=format&fit=crop",
    rating: 4.6,
    prepTime: "12-18 min"
  },
  {
    id: "5",
    name: "Midnight Chocolate Cake",
    description: "Rich dark chocolate layers with raspberry coulis and gold leaf.",
    price: 8.99,
    category: "Desserts",
    image: "https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=500&auto=format&fit=crop",
    rating: 5.0,
    prepTime: "5 min"
  },
  {
    id: "6",
    name: "Spicy Pepperoni",
    description: "Classic pepperoni with a kick of jalapeños and hot honey drizzle.",
    price: 16.99,
    category: "Pizza",
    image: "https://images.unsplash.com/photo-1628840042765-356cda07504e?q=80&w=500&auto=format&fit=crop",
    rating: 4.8,
    prepTime: "20-25 min"
  }
];
