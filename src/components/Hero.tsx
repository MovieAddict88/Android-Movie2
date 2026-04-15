"use client";

import React from "react";
import { motion } from "framer-motion";
import { Button } from "./ui/Button";
import { ArrowRight, Star, Clock, ShieldCheck } from "lucide-react";

const Hero = () => {
  return (
    <section className="relative min-h-screen flex items-center pt-20 overflow-hidden">
      {/* Background Decor */}
      <div className="absolute top-0 right-0 -z-10 w-2/3 h-full modern-fluid-gradient opacity-10 blur-[120px] rounded-full transform translate-x-1/4 -translate-y-1/4" />
      <div className="absolute bottom-0 left-0 -z-10 w-1/2 h-2/3 bg-secondary/10 blur-[100px] rounded-full transform -translate-x-1/4 translate-y-1/4" />

      <div className="container mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
        <motion.div
          initial={{ opacity: 0, x: -50 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.8 }}
        >
          <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary font-medium mb-6 animate-fade-in">
            <Star className="w-4 h-4 fill-primary" />
            <span>#1 Food Delivery Service in Town</span>
          </div>
          <h1 className="text-6xl md:text-8xl font-black leading-tight mb-6">
            Satisfy Your <br />
            <span className="text-primary italic">Cravings</span> Faster.
          </h1>
          <p className="text-xl text-gray-600 dark:text-gray-400 mb-10 max-w-lg leading-relaxed">
            Experience the future of food delivery with VibeEats. Modern flavors,
            fluid ordering, and lightning fast delivery to your doorstep.
          </p>
          <div className="flex flex-col sm:flex-row gap-4">
            <Button size="lg" className="group">
              Order Now
              <ArrowRight className="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" />
            </Button>
            <Button variant="outline" size="lg">View Menu</Button>
          </div>

          <div className="mt-12 flex items-center gap-8">
            <div className="flex flex-col">
              <span className="text-2xl font-bold">50k+</span>
              <span className="text-sm text-gray-500">Happy Users</span>
            </div>
            <div className="w-px h-10 bg-gray-200 dark:bg-gray-800" />
            <div className="flex flex-col">
              <span className="text-2xl font-bold">4.9</span>
              <span className="text-sm text-gray-500">Rating</span>
            </div>
          </div>
        </motion.div>

        <motion.div
          initial={{ opacity: 0, scale: 0.8, rotate: 5 }}
          animate={{ opacity: 1, scale: 1, rotate: 0 }}
          transition={{ duration: 1, type: "spring" }}
          className="relative"
        >
          <div className="relative z-10 w-full aspect-square rounded-[4rem] modern-fluid-gradient shadow-2xl p-1 overflow-hidden">
            <div className="w-full h-full bg-white dark:bg-vibe-black rounded-[3.8rem] flex items-center justify-center p-8">
               {/* Mock UI Element */}
               <div className="w-full space-y-6">
                  <div className="h-48 w-full bg-gray-100 dark:bg-gray-800 rounded-3xl animate-pulse" />
                  <div className="space-y-3">
                    <div className="h-8 w-2/3 bg-gray-100 dark:bg-gray-800 rounded-lg animate-pulse" />
                    <div className="h-4 w-full bg-gray-100 dark:bg-gray-800 rounded-lg animate-pulse" />
                    <div className="h-4 w-1/2 bg-gray-100 dark:bg-gray-800 rounded-lg animate-pulse" />
                  </div>
                  <div className="flex justify-between items-center">
                    <div className="h-10 w-24 bg-primary/20 rounded-xl animate-pulse" />
                    <div className="h-12 w-12 bg-secondary/20 rounded-full animate-pulse" />
                  </div>
               </div>
            </div>
          </div>

          {/* Floating badges */}
          <motion.div
            animate={{ y: [0, -15, 0] }}
            transition={{ repeat: Infinity, duration: 4, ease: "easeInOut" }}
            className="absolute top-10 -left-10 z-20 glass-morphism p-4 rounded-2xl flex items-center gap-3 shadow-xl"
          >
            <div className="bg-secondary p-2 rounded-xl text-white">
              <Clock className="w-5 h-5" />
            </div>
            <div>
              <p className="text-xs text-gray-500">Delivery</p>
              <p className="font-bold">20-30 Min</p>
            </div>
          </motion.div>

          <motion.div
            animate={{ y: [0, 15, 0] }}
            transition={{ repeat: Infinity, duration: 5, ease: "easeInOut", delay: 1 }}
            className="absolute bottom-10 -right-5 z-20 glass-morphism p-4 rounded-2xl flex items-center gap-3 shadow-xl"
          >
            <div className="bg-primary p-2 rounded-xl text-white">
              <ShieldCheck className="w-5 h-5" />
            </div>
            <div>
              <p className="text-xs text-gray-500">Quality</p>
              <p className="font-bold">Top Verified</p>
            </div>
          </motion.div>
        </motion.div>
      </div>
    </section>
  );
};

export { Hero };
