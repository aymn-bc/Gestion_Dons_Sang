/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",           // Root PHP files
    "./**/**/*.php",        // PHP files in all subdirectories
    "./auth/**/*.php",      // Specific auth directory
    "./config/**/*.php",    // Specific config directory
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}