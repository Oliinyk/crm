const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
  purge: [
    // prettier-ignore
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  darkMode: false, // or 'media' or 'class'
  theme: {
    colors: {
      white: '#fff',
      transparent: 'transparent',
      current: 'currentColor',
      primary: {
        opacity: '#e3f0ea',
        light: '#A3CDBB',
        DEFAULT: '#8CC0AA',
        dark: '#709A88',
      },
      secondary: {
        light: '#8B93C4',
        DEFAULT: '#6E78B5',
        dark: '#586091',
      },
      accent: {
        light: '#F1F3FF',
        DEFAULT: '#E9ECFF',
        dark: '#CDD0E0',
      },
      dark: '#26293B',
      gray: {
        light: '#D2D3DA',
        DEFAULT: '#898C98',
      },
      light: '#F1F5F4',
      red: {
        opacity: '#fbe2de',
        light: '#F3A095',
        DEFAULT: '#ED6A5A',
        dark: '#D15D4F',
      },
      orange: {
        opacity: '#fdf2dd',
        light: '#F7D38E',
        DEFAULT: '#F3BB4F',
        dark: '#D6A546',
      },
    },
    zIndex: {
      '0': 0,
      '1': 1,
      '2': 2,
      '10': 10,
      '20': 20,
      '25': 25,
      '30': 30,
      '40': 40,
      '49': 49,
      '50': 50,
      '75': 75,
      '100': 100,
      '99999': 99999,
      'auto': 'auto',
    },
    screen: {
      ...defaultTheme.screens,
    },
    fontSize: {
      'xs': ['.714rem', '1.25'],
      'sm': ['.857rem', '1.25'],
      'base': ['1rem', '1.43'],
      'lg': ['1.286rem', '1.25'],
      'xl': ['1.714rem', '1.25'],
      '2xl': ['2.857rem', '1.25'],
      '3xl': ['4.571rem', '1.25'],
    },
    borderWidth: {
      DEFAULT: '1px',
      '0': '0',
      '2': '2px',
      '3': '3px',
      '4': '4px',
      '6': '6px',
      '8': '8px',
    },
    boxShadow: theme => ({
      DEFAULT: '0 2px 4px 0 rgba(0, 0, 0, 0.25)',
      md: '0 2px 3px 0 rgba(0, 0, 0, 0.25)',
      'border-light': 'inset 0px 0px 0px 1px ' + theme('colors.light'),
      'border-red': 'inset 0px 0px 0px 1px ' + theme('colors.red.DEFAULT'),
      none: 'none',
    }),
    extend: {
      inset: {
        '1/6': '15%',
        '1/65': '13%',
      },
      fontFamily: {
        sans: ['Mulish', ...defaultTheme.fontFamily.sans],
      },
      borderColor: theme => ({
        DEFAULT: theme('colors.light', 'currentColor'),
      }),
      transitionProperty: {
        'max-h': 'max-height',
      },
      maxHeight: {
        'screen-70': '70vh',
        'screen-80': '80vh',
        'screen-90': '90vh',
      },
      minHeight: {
        'screen-80': '80vh',
      },
      minWidth: {
        '1/2': '50%',
        '100': '100px',
        '130': '130px',
        '150': '150px',
        '200': '200px',
        '230': '230px',
        '330': '330px',
      },
      margin: {
        '-81': '-17.1rem',
        '-51': '-12.65rem',
        '-1/2': '-50%',
        '-1/3': '-33.333333%',
        '-1/4': '-25%',
        '-1/5': '-20%',
        '-1/6': '-16.666667%',
      },
    },
    fill: theme => theme('colors'),
  },
  plugins: [],
  variants: {
    extend: {
      backgroundColor: ['active', 'disabled'],
      textColor: ['active', 'disabled'],
      fill: ['group-hover', 'hover'],
    },
  },
}
