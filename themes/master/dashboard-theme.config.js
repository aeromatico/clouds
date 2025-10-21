/**
 * Dashboard Theme Configuration
 *
 * This file contains the DaisyUI theme configuration for the dashboard
 * Separated for easy multisitio management
 *
 * @version 2.0.0 - Modern Design System
 */

export default {
  themes: [
    {
      light: {
        // Primary colors
        "primary": "#d1a411ff",
        "primary-content": "#ffffff",

        // Secondary colors
        "secondary": "#64748b",
        "secondary-content": "#ffffff",

        // Accent colors
        "accent": "#f1f5f9",
        "accent-content": "#1e293b",

        // Neutral colors
        "neutral": "#1e293b",
        "neutral-content": "#f1f5f9",

        // Base colors
        "base-100": "#ffffff",
        "base-200": "#f8fafc",
        "base-300": "#f1f5f9",
        "base-content": "#1e293b",

        // State colors
        "info": "#0ea5e9",
        "info-content": "#ffffff",

        "success": "#10b981",
        "success-content": "#ffffff",

        "warning": "#f59e0b",
        "warning-content": "#ffffff",

        "error": "#ef4444",
        "error-content": "#ffffff",

        // Modern design tokens
        "--rounded-box": "1rem",           // card border radius
        "--rounded-btn": "0.75rem",        // button border radius
        "--rounded-badge": "1rem",         // badge border radius
        "--animation-btn": "0.25s",        // button animation duration
        "--animation-input": "0.2s",       // input animation duration
        "--btn-focus-scale": "1",          // button focus scale (no scale)
        "--border-btn": "1px",             // button border width
        "--tab-border": "1px",             // tab border width
        "--tab-radius": "0.75rem",         // tab border radius
      },
      dark: {
        // Primary colors
        "primary": "#d1a411ff",
        "primary-content": "#020617",

        // Secondary colors
        "secondary": "#94a3b8",
        "secondary-content": "#020617",

        // Accent colors
        "accent": "#1e293b",
        "accent-content": "#f1f5f9",

        // Neutral colors
        "neutral": "#f1f5f9",
        "neutral-content": "#020617",

        // Base colors
        "base-100": "#020617",
        "base-200": "#0f172a",
        "base-300": "#1e293b",
        "base-content": "#f1f5f9",

        // State colors
        "info": "#38bdf8",
        "info-content": "#020617",

        "success": "#10b981",
        "success-content": "#020617",

        "warning": "#f59e0b",
        "warning-content": "#020617",

        "error": "#ef4444",
        "error-content": "#ffffff",

        // Modern design tokens
        "--rounded-box": "1rem",
        "--rounded-btn": "0.75rem",
        "--rounded-badge": "1rem",
        "--animation-btn": "0.25s",
        "--animation-input": "0.2s",
        "--btn-focus-scale": "1",
        "--border-btn": "1px",
        "--tab-border": "1px",
        "--tab-radius": "0.75rem",
      },
    },
  ],
  darkTheme: "dark",
  base: true,
  styled: true,
  utils: true,
  prefix: "",
  logs: true,
  themeRoot: ":root",
};
