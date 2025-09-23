import type { Config } from "tailwindcss";
import plugin from 'tailwindcss/plugin';

const config: Config = {
  darkMode: 'class',
  content: [
    "./resources/**/*.{blade.php,vue,js,ts}",
  ],
  theme: {
    fontFamily: {
      sans: ["Noto Sans JP", "Noto Sans", "system-ui", "sans-serif"],
    },
    extend: {
      colors: {
          // CSS参照カラー
          'theme-bg-main': 'var(--color-bg-main)',
          'theme-bg-subtle': 'var(--color-bg-subtle)',
          'theme-bg-card': 'var(--color-bg-card)',
          'theme-bg-accent': 'var(--color-bg-accent)',
          'theme-bg-accent-subtle': 'var(--color-bg-accent-subtle)',

          'theme-text-primary': 'var(--color-text-primary)',
          'theme-text-secondary': 'var(--color-text-secondary)',
          'theme-text-interactive-primary': 'var(--color-text-interactive-primary)',

          'theme-border': 'var(--color-border)',
          'theme-border-subtle': 'var(--color-border-subtle)',

          'theme-interactive-primary': 'var(--color-interactive-primary)',
          'theme-interactive-secondary': 'var(--color-interactive-secondary)',
          'theme-interactive-danger': 'var(--color-interactive-danger)',
          'theme-interactive-focus-ring': 'var(--color-interactive-focus-ring)',
          'theme-gradient-start': 'var(--color-gradient-start)',
          'theme-gradient-end': 'var(--color-gradient-end)',

          'theme-accent': 'var(--color-accent)',

          // 白・黒背景で見やすい水色
          primary: {
              50: "#F0F9FF",
              100: "#E0F2FE",
              200: "#BEE3F8",
              300: "#90CDF4",
              400: "#63B3ED",
              500: "#4299E1",
              600: "#3182CE",
              700: "#2B6CB0",
              800: "#2C5282",
              900: "#2A4365",
          },
          // 薄っすらと青みがかったモノクロ
          secondary: {
              50: "#F7F8FA",
              100: "#EFEFF4",
              200: "#E3E4E8",
              300: "#D1D3D8",
              400: "#AEB1B8",
              500: "#868A91",
              600: "#686C73",
              700: "#55585E",
              800: "#363A3F",
              900: "#222429",
          },
          // 別の色相の水色
          accent: {
              50: "#E5F9FF",
              100: "#D0F4FF",
              200: "#B8F0FF",
              300: "#99E8FF",
              400: "#70E0FF",
              500: "#44D0F8",
              600: "#29C0F1",
              700: "#18ACE8",
              800: "#0997D6",
              900: "#0283C1",
          },

          // メモの状態を表す色
          'status-working': '#FBBF24',      // 作業中 - 黄色
          'status-important': '#EF4444', // 重要 - 赤色
          'status-later': '#9CA3AF',    // 後で - 灰色
      },
    },
  },
  plugins: [
      plugin(function({ addComponents }) {
          addComponents({
              '.btn-disabled': {
                  '&:disabled': {
                      filter: 'brightness(1.1) saturate(.1)',
                      cursor: 'not-allowed',
                  },
                  '.dark &:disabled': {
                      filter: 'brightness(0.75) saturate(.1)',
                      cursor: 'not-allowed',
                  },
              },
              '.hover-dim': {
                  '&:hover': {
                      filter: 'brightness(0.9)',
                  },
                  '.dark &:hover': {
                      filter: 'brightness(1.25)',
                  },
              },
          })
      })
  ],
};

export default config;
