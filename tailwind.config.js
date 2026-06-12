/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{js,jsx,vue}',
  ],
  theme: {
    extend: {
      colors: {
        // Background colors (dark theme)
        'bg-primary': '#0F0F10',
        'bg-secondary': '#17171A',
        'bg-tertiary': '#1E1E22',
        
        // Border colors
        'border-light': '#2A2A2F',
        'border-medium': '#333338',
        
        // Text colors
        'text-primary': '#F0F0F2',
        'text-secondary': '#A0A0AC',
        'text-tertiary': '#65656F',
        
        // Accent colors
        'accent-violet': '#6C63FF',
        'accent-green': '#22C97A',
        'accent-amber': '#F5A623',
        'accent-red': '#FF4C4C',
        'accent-blue': '#3B9EFF',
      },
      fontFamily: {
        'syne': ['Syne', 'system-ui', 'sans-serif'],
        'inter': ['Inter', 'system-ui', 'sans-serif'],
        'mono': ['JetBrains Mono', 'monospace'],
      },
      fontSize: {
        // Display (Syne 700)
        'display-lg': ['80px', { lineHeight: '1', letterSpacing: '-0.03em', fontWeight: '700' }],
        'display-md': ['64px', { lineHeight: '1.1', letterSpacing: '-0.03em', fontWeight: '700' }],
        'display-sm': ['48px', { lineHeight: '1.2', letterSpacing: '-0.03em', fontWeight: '700' }],
        
        // Headings (Inter 600)
        'heading-xl': ['36px', { lineHeight: '1.3', letterSpacing: '-0.02em', fontWeight: '600' }],
        'heading-lg': ['28px', { lineHeight: '1.4', letterSpacing: '-0.02em', fontWeight: '600' }],
        'heading-md': ['24px', { lineHeight: '1.4', letterSpacing: '-0.02em', fontWeight: '600' }],
        'heading-sm': ['20px', { lineHeight: '1.5', letterSpacing: '-0.02em', fontWeight: '600' }],
        
        // Body text (Inter 400)
        'body-lg': ['16px', { lineHeight: '1.7', fontWeight: '400' }],
        'body-md': ['15px', { lineHeight: '1.7', fontWeight: '400' }],
        'body-sm': ['14px', { lineHeight: '1.7', fontWeight: '400' }],
        
        // Labels (Inter 500-600)
        'label-lg': ['12px', { lineHeight: '1.5', letterSpacing: '0.08em', fontWeight: '600', textTransform: 'uppercase' }],
        'label-md': ['11px', { lineHeight: '1.5', letterSpacing: '0.08em', fontWeight: '500', textTransform: 'uppercase' }],
        'label-sm': ['10px', { lineHeight: '1.5', letterSpacing: '0.08em', fontWeight: '500', textTransform: 'uppercase' }],
      },
      spacing: {
        'xs': '4px',
        'sm': '8px',
        'md': '12px',
        'lg': '16px',
        'xl': '24px',
        '2xl': '32px',
        '3xl': '48px',
        '4xl': '64px',
        '5xl': '80px',
        '6xl': '96px',
      },
      borderRadius: {
        'xs': '6px',
        'sm': '6px',
        'md': '10px',
        'lg': '14px',
        'xl': '20px',
      },
      boxShadow: {
        'sm': '0 2px 8px rgba(0, 0, 0, 0.12)',
        'md': '0 4px 16px rgba(0, 0, 0, 0.15)',
        'lg': '0 8px 24px rgba(0, 0, 0, 0.20)',
        'none': 'none',
      },
      backgroundImage: {
        'gradient-violet': 'linear-gradient(135deg, #6C63FF 0%, #8B7FFF 100%)',
        'gradient-green': 'linear-gradient(135deg, #22C97A 0%, #2FE08F 100%)',
      },
    },
  },
  plugins: [],
}
