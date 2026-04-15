"use client";

import React, { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Card } from "./ui/Card";
import { Button } from "./ui/Button";
import { Star, Clock, Plus } from "lucide-react";
import { MENU_ITEMS, CATEGORIES, FoodItem } from "@/data/menu";

interface MenuProps {
  onAddToCart: (item: FoodItem) => void;
}

const Menu = ({ onAddToCart }: MenuProps) => {
  const [activeCategory, setActiveCategory] = useState("All");

  const filteredItems = MENU_ITEMS.filter(
    (item) => activeCategory === "All" || item.category === activeCategory
  );

  return (
    <section id="menu" className="py-24 bg-gray-50 dark:bg-gray-950">
      <div className="container mx-auto px-6">
        <div className="text-center mb-16">
          <h2 className="text-4xl md:text-5xl font-bold mb-4">Explore Our Menu</h2>
          <p className="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            From gourmet burgers to fresh sushi, we have something to satisfy every craving.
            Filter by category to find your perfect meal.
          </p>
        </div>

        {/* Categories */}
        <div className="flex flex-wrap justify-center gap-3 mb-12">
          {CATEGORIES.map((category) => (
            <button
              key={category}
              onClick={() => setActiveCategory(category)}
              className={`px-6 py-2 rounded-full font-medium transition-all duration-300 ${
                activeCategory === category
                  ? "bg-primary text-white shadow-lg"
                  : "bg-white dark:bg-vibe-black text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
              }`}
            >
              {category}
            </button>
          ))}
        </div>

        {/* Food Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <AnimatePresence mode="popLayout">
            {filteredItems.map((item) => (
              <motion.div
                key={item.id}
                layout
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.9 }}
                transition={{ duration: 0.3 }}
              >
                <Card className="h-full flex flex-col p-0 overflow-hidden">
                  <div className="relative h-64 w-full">
                    <img
                      src={item.image}
                      alt={item.name}
                      className="w-full h-full object-cover transition-transform duration-500 hover:scale-110"
                    />
                    <div className="absolute top-4 right-4 glass-morphism px-3 py-1 rounded-full flex items-center gap-1">
                      <Star className="w-4 h-4 text-yellow-500 fill-yellow-500" />
                      <span className="text-sm font-bold">{item.rating}</span>
                    </div>
                  </div>
                  <div className="p-6 flex-grow flex flex-col">
                    <div className="flex justify-between items-start mb-2">
                      <h3 className="text-xl font-bold">{item.name}</h3>
                      <span className="text-primary font-bold text-lg">${item.price.toFixed(2)}</span>
                    </div>
                    <p className="text-gray-600 dark:text-gray-400 text-sm mb-6 line-clamp-2">
                      {item.description}
                    </p>
                    <div className="mt-auto flex items-center justify-between">
                      <div className="flex items-center gap-2 text-gray-500 text-sm">
                        <Clock className="w-4 h-4" />
                        <span>{item.prepTime}</span>
                      </div>
                      <Button
                        size="sm"
                        onClick={() => onAddToCart(item)}
                        className="rounded-xl px-4 py-2"
                      >
                        <Plus className="w-4 h-4 mr-1" /> Add
                      </Button>
                    </div>
                  </div>
                </Card>
              </motion.div>
            ))}
          </AnimatePresence>
        </div>
      </div>
    </section>
  );
};

export { Menu };
