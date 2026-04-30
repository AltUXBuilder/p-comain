import defaultTheme from 'tailwindcss/defaultTheme'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './app/Http/Livewire/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        plum: {
          50:  '#f5f0f7',
          100: '#ebe1ef',
          200: '#d5c3de',
          300: '#b99ac6',
          400: '#9a6daa',
          500: '#7d4f8e',
          600: '#663d75',
          700: '#553060',
          800: '#4A3050', // PRIMARY DARK — deep plum
          900: '#3a2540',
          950: '#271929',
        },
        lilac: {
          50:  '#faf7fc',
          100: '#f4eef9',
          200: '#ecddf4',
          300: '#dfc3eb',
          400: '#cca3dd',
          500: '#C9A8D4', // PRIMARY ACCENT — soft lilac
          600: '#a97bbf',
          700: '#8e61a3',
          800: '#754f87',
          900: '#60426e',
          950: '#3f2849',
        },
        brand: {
          plum:  '#4A3050',
          lilac: '#C9A8D4',
          white: '#FFFFFF',
        },
      },
      fontFamily: {
        display: ['"Cormorant Garamond"', ...defaultTheme.fontFamily.serif],
        sans:    ['"DM Sans"', ...defaultTheme.fontFamily.sans],
      },
      fontSize: {
        'display-xl': ['4.5rem',  { lineHeight: '1.05', letterSpacing: '-0.02em' }],
        'display-lg': ['3.5rem',  { lineHeight: '1.08', letterSpacing: '-0.02em' }],
        'display-md': ['2.5rem',  { lineHeight: '1.1',  letterSpacing: '-0.01em' }],
        'display-sm': ['2rem',    { lineHeight: '1.15', letterSpacing: '-0.01em' }],
      },
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
        '88': '22rem',
        '128': '32rem',
      },
      borderRadius: {
        'xl':  '0.875rem',
        '2xl': '1.25rem',
        '3xl': '2rem',
      },
      boxShadow: {
        'plum-sm': '0 2px 8px 0 rgba(74,48,80,0.10)',
        'plum':    '0 4px 20px 0 rgba(74,48,80,0.15)',
        'plum-lg': '0 8px 40px 0 rgba(74,48,80,0.20)',
        'lilac':   '0 4px 20px 0 rgba(201,168,212,0.30)',
      },
      backgroundImage: {
        'plum-gradient':  'linear-gradient(135deg, #4A3050 0%, #5B3E6A 100%)',
        'lilac-gradient': 'linear-gradient(135deg, #C9A8D4 0%, #dfc3eb 100%)',
        'hero-pattern':   "url('/images/brand/hero-pattern.svg')",
      },
      animation: {
        'fade-in':    'fadeIn 0.4s ease-out',
        'slide-up':   'slideUp 0.4s ease-out',
        'slide-down': 'slideDown 0.3s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%':   { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%':   { opacity: '0', transform: 'translateY(16px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideDown: {
          '0%':   { opacity: '0', transform: 'translateY(-8px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
