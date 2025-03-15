/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        "dodgeroll-gold": "#F79F1A",
        "apple-green": "#046E1B",
        "dire-wolf": "#292727",
        "back-gray": "#f8f9fa",
      }
    },
    fontFamily: { 
      // Montserrat: ["Montserrat", "sans-serif"],
      // OpenSans: ["'Open Sans'", "sans-serif"], 
      satoshi: ["Satoshi", "sans-serif"] 
    },
    container:{
      center: true,
      padding: "2rem"
    }, 
  },
  plugins: [],
}

