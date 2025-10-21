// Import CSS
import '../css/app.css';

// Hot reload test - JS file modified
console.log('HMR Test: App.js cargado -', new Date().toLocaleTimeString());

// Import Alpine.js
import Alpine from 'alpinejs';

// Import API Management System
import './core/api-manager.js';
import './core/alpine-api-mixin.js';

// Import Microfrontends
import './microfrontends/user-dashboard.js';
import './microfrontends/user-profile.js';

// Import Components
import './components/redis-monitor.js';
import dashboard from './components/dashboard.js';
import planCard from './components/plan-card.js';
import ShoppingCart from './cart.js';

// Register components
Alpine.data('dashboard', dashboard);
Alpine.data('planCard', planCard);

// Initialize shopping cart
window.cart = new ShoppingCart();

// Import PWA functionality
import { registerSW } from 'virtual:pwa-register';

// Theme Management Store
Alpine.store('theme', {
  current: localStorage.getItem('theme') || 'system',
  
  init() {
    // Forzar aplicación inicial
    this.apply();
    
    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
      if (this.current === 'system') {
        this.apply();
      }
    });
  },
  
  set(theme) {
    this.current = theme;
    localStorage.setItem('theme', theme);
    this.apply();
    
    // Forzar actualización del DOM para asegurar reactividad
    this.$nextTick && this.$nextTick(() => {
      // Trigger any watchers
    });
  },
  
  apply() {
    const isDark = this.current === 'dark' || 
      (this.current === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    
    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    document.documentElement.classList.toggle('dark', isDark);
    
    // Disparar evento personalizado para mayor compatibilidad
    window.dispatchEvent(new CustomEvent('themeChanged', { 
      detail: { isDark, theme: this.current } 
    }));
  },
  
  get isDark() {
    return this.current === 'dark' || 
           (this.current === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
  },
  
  toggle() {
    // Simple toggle between light and dark only
    const newTheme = this.isDark ? 'light' : 'dark';
    this.set(newTheme);
  },

  toggleWithSystem() {
    // Cycle through light, dark, and system (for advanced toggle)
    const themes = ['light', 'dark', 'system'];
    const currentIndex = themes.indexOf(this.current);
    const nextIndex = (currentIndex + 1) % themes.length;
    this.set(themes[nextIndex]);
  }
});

// Navigation Store
Alpine.store('navigation', {
  isOpen: false,
  
  toggle() {
    this.isOpen = !this.isOpen;
  },
  
  close() {
    this.isOpen = false;
  }
});

// Toast Notification Store
Alpine.store('toast', {
  notifications: [],
  
  show(message, type = 'info', duration = 5000) {
    const id = Date.now();
    const notification = { id, message, type, duration };
    
    this.notifications.push(notification);
    
    if (duration > 0) {
      setTimeout(() => {
        this.remove(id);
      }, duration);
    }
    
    return id;
  },
  
  remove(id) {
    const index = this.notifications.findIndex(n => n.id === id);
    if (index > -1) {
      this.notifications.splice(index, 1);
    }
  },
  
  success(message, duration) {
    return this.show(message, 'success', duration);
  },
  
  error(message, duration) {
    return this.show(message, 'error', duration);
  },
  
  warning(message, duration) {
    return this.show(message, 'warning', duration);
  },
  
  info(message, duration) {
    return this.show(message, 'info', duration);
  }
});

// PWA Store
Alpine.store('pwa', {
  installPrompt: null,
  isInstallable: false,
  isInstalled: false,
  
  init() {
    // Check if app is already installed
    this.isInstalled = window.matchMedia('(display-mode: standalone)').matches ||
                     window.navigator.standalone === true;
    
    // Listen for install prompt
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      this.installPrompt = e;
      this.isInstallable = true;
    });
    
    // Listen for app installed
    window.addEventListener('appinstalled', () => {
      this.isInstalled = true;
      this.isInstallable = false;
      this.installPrompt = null;
      Alpine.store('toast').success('App installed successfully!');
    });
  },
  
  async install() {
    if (!this.installPrompt) return false;
    
    const result = await this.installPrompt.prompt();
    
    if (result.outcome === 'accepted') {
      this.isInstallable = false;
      this.installPrompt = null;
    }
    
    return result.outcome === 'accepted';
  }
});

// Theme Toggle Component  
Alpine.data('themeToggle', () => ({
  init() {
    // Aplicar tema inicial
    this.applyTheme();
  },
  
  get isDark() {
    return document.documentElement.classList.contains('dark');
  },
  
  applyTheme() {
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const shouldBeDark = saved === 'dark' || (saved !== 'light' && prefersDark);
    
    document.documentElement.classList.toggle('dark', shouldBeDark);
    document.documentElement.setAttribute('data-theme', shouldBeDark ? 'dark' : 'light');
  },
  
  toggle() {
    const isCurrentlyDark = this.isDark;
    const newTheme = isCurrentlyDark ? 'light' : 'dark';
    
    localStorage.setItem('theme', newTheme);
    document.documentElement.classList.toggle('dark', newTheme === 'dark');
    document.documentElement.setAttribute('data-theme', newTheme);
  }
}));

// Mobile Navigation Component
Alpine.data('mobileNav', () => ({
  get isOpen() {
    return Alpine.store('navigation').isOpen;
  },
  
  toggle() {
    Alpine.store('navigation').toggle();
  },
  
  close() {
    Alpine.store('navigation').close();
  }
}));

// Modal Component
Alpine.data('modal', (initialOpen = false) => ({
  open: initialOpen,
  
  show() {
    this.open = true;
    document.body.style.overflow = 'hidden';
  },
  
  hide() {
    this.open = false;
    document.body.style.overflow = '';
  },
  
  toggle() {
    this.open ? this.hide() : this.show();
  }
}));

// Dropdown Component
Alpine.data('dropdown', () => ({
  open: false,
  
  toggle() {
    this.open = !this.open;
  },
  
  close() {
    this.open = false;
  }
}));

// Carousel Component
Alpine.data('carousel', (items = [], autoplay = true, interval = 5000) => ({
  items,
  currentIndex: 0,
  autoplay,
  interval,
  timer: null,
  
  init() {
    if (this.autoplay && this.items.length > 1) {
      this.startAutoplay();
    }
  },
  
  get currentItem() {
    return this.items[this.currentIndex];
  },
  
  next() {
    this.currentIndex = (this.currentIndex + 1) % this.items.length;
    this.resetAutoplay();
  },
  
  previous() {
    this.currentIndex = this.currentIndex === 0 ? this.items.length - 1 : this.currentIndex - 1;
    this.resetAutoplay();
  },
  
  goTo(index) {
    this.currentIndex = index;
    this.resetAutoplay();
  },
  
  startAutoplay() {
    if (!this.autoplay) return;
    
    this.timer = setInterval(() => {
      this.next();
    }, this.interval);
  },
  
  stopAutoplay() {
    if (this.timer) {
      clearInterval(this.timer);
      this.timer = null;
    }
  },
  
  resetAutoplay() {
    this.stopAutoplay();
    if (this.autoplay) {
      this.startAutoplay();
    }
  }
}));

// Tabs Component
Alpine.data('tabs', (initialTab = 0) => ({
  activeTab: initialTab,
  
  setTab(index) {
    this.activeTab = index;
  },
  
  isActive(index) {
    return this.activeTab === index;
  }
}));

// Accordion Component
Alpine.data('accordion', (initialOpen = null, allowMultiple = false) => ({
  openItems: initialOpen !== null ? [initialOpen] : [],
  allowMultiple,
  
  toggle(index) {
    if (this.isOpen(index)) {
      this.close(index);
    } else {
      this.open(index);
    }
  },
  
  open(index) {
    if (!this.allowMultiple) {
      this.openItems = [index];
    } else {
      if (!this.openItems.includes(index)) {
        this.openItems.push(index);
      }
    }
  },
  
  close(index) {
    this.openItems = this.openItems.filter(item => item !== index);
  },
  
  isOpen(index) {
    return this.openItems.includes(index);
  }
}));

// Form Validation Component
Alpine.data('formValidation', (rules = {}) => ({
  errors: {},
  rules,
  
  validate(field, value) {
    const fieldRules = this.rules[field];
    if (!fieldRules) return true;
    
    const errors = [];
    
    for (const rule of fieldRules) {
      const result = rule.validator(value);
      if (!result) {
        errors.push(rule.message);
      }
    }
    
    if (errors.length > 0) {
      this.errors[field] = errors;
      return false;
    } else {
      delete this.errors[field];
      return true;
    }
  },
  
  validateAll(formData) {
    let isValid = true;
    
    for (const field in this.rules) {
      const fieldValid = this.validate(field, formData[field]);
      if (!fieldValid) {
        isValid = false;
      }
    }
    
    return isValid;
  },
  
  hasError(field) {
    return field in this.errors;
  },
  
  getError(field) {
    return this.errors[field]?.[0] || '';
  },
  
  clearErrors() {
    this.errors = {};
  }
}));

// Search Component
Alpine.data('search', (items = [], searchFields = ['name']) => ({
  query: '',
  items,
  searchFields,
  
  get filteredItems() {
    if (!this.query.trim()) {
      return this.items;
    }
    
    const query = this.query.toLowerCase();
    
    return this.items.filter(item => {
      return this.searchFields.some(field => {
        const value = this.getNestedProperty(item, field);
        return String(value).toLowerCase().includes(query);
      });
    });
  },
  
  getNestedProperty(obj, path) {
    return path.split('.').reduce((current, key) => current?.[key], obj);
  },
  
  clear() {
    this.query = '';
  }
}));

// Lazy Loading Component
Alpine.data('lazyLoad', () => ({
  loaded: false,
  
  init() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          this.loaded = true;
          observer.unobserve(entry.target);
        }
      });
    });
    
    observer.observe(this.$el);
  }
}));

// PWA Install Component
Alpine.data('pwaInstall', () => ({
  show: false,
  
  init() {
    // Esperar a que PWA store esté listo y mostrar después de 3 segundos
    setTimeout(() => {
      // Mostrar si es instalable O si estamos en modo privado/testing
      const isPrivateMode = !window.navigator.onLine || window.location.protocol === 'http:';
      const shouldShow = Alpine.store('pwa').isInstallable || 
                        (!Alpine.store('pwa').isInstalled && !document.hidden);
      
      if (shouldShow) {
        this.show = true;
      }
      
      console.log('PWA Install Check:', {
        isInstallable: Alpine.store('pwa').isInstallable,
        isInstalled: Alpine.store('pwa').isInstalled,
        shouldShow: shouldShow,
        show: this.show
      });
    }, 3000);
    
    // Escuchar cambios en el store PWA
    this.$watch('$store.pwa.isInstallable', (value) => {
      if (!value) {
        this.show = false;
      }
    });
  },
  
  get isInstallable() {
    return Alpine.store('pwa').isInstallable;
  },
  
  get isInstalled() {
    return Alpine.store('pwa').isInstalled;
  },
  
  async install() {
    if (Alpine.store('pwa').installPrompt) {
      const success = await Alpine.store('pwa').install();
      if (success) {
        this.show = false;
      }
      return success;
    } else {
      // Fallback para cuando no hay prompt nativo
      Alpine.store('toast').info('Para instalar: usa el menú del navegador → "Instalar aplicación"', 8000);
      return false;
    }
  },
  
  dismiss() {
    this.show = false;
    Alpine.store('pwa').isInstallable = false;
    Alpine.store('pwa').installPrompt = null;
  }
}));

// Toast Component
Alpine.data('toastContainer', () => ({
  get notifications() {
    return Alpine.store('toast').notifications;
  },
  
  remove(id) {
    Alpine.store('toast').remove(id);
  }
}));

// Pricing App Component
Alpine.data('pricingApp', () => ({
  isAnnual: false,
  showMoreFeatures: false,
  plans: [
    {
      name: 'Basic',
      description: 'Everything needed for your website',
      monthlyPrice: '3.63',
      annualPrice: '3.08',
      renewalText: '$6.99/mo when you renew',
      isPopular: false,
      features: [
        { name: '1 Website', available: true },
        { name: 'Standard Performance', available: true },
        { name: '24/7/365 Support', available: true },
        { name: 'Free Email', available: false },
        { name: 'Unlimited Bandwidth', available: false },
        { name: '100 GB SSD Storage', available: false },
        { name: 'Unlimited Free SSL', available: true },
        { name: '99.9% Uptime Guarantee', available: true },
        { name: 'Web Application Firewall', available: false }
      ],
      additionalFeatures: [
        { name: 'Daily Backups', available: false },
        { name: 'CDN Integration', available: false },
        { name: 'Priority Support', available: false }
      ]
    },
    {
      name: 'Premium',
      description: 'Level-up with more power features',
      monthlyPrice: '6.63',
      annualPrice: '5.63',
      renewalText: '$6.99/mo when you renew',
      isPopular: false,
      features: [
        { name: '1 Website', available: true },
        { name: 'Standard Performance', available: true },
        { name: '24/7/365 Support', available: true },
        { name: 'Free Email', available: false },
        { name: 'Unlimited Bandwidth', available: false },
        { name: '100 GB SSD Storage', available: false },
        { name: 'Unlimited Free SSL', available: true },
        { name: '99.9% Uptime Guarantee', available: true },
        { name: 'Web Application Firewall', available: true }
      ],
      additionalFeatures: [
        { name: 'Daily Backups', available: true },
        { name: 'CDN Integration', available: false },
        { name: 'Priority Support', available: false }
      ]
    },
    {
      name: 'Business',
      description: 'Everything needed for your website',
      monthlyPrice: '8.63',
      annualPrice: '7.33',
      renewalText: '$6.99/mo when you renew',
      isPopular: true,
      features: [
        { name: '1 Website', available: true },
        { name: 'Standard Performance', available: true },
        { name: '24/7/365 Support', available: true },
        { name: 'Free Email', available: false },
        { name: 'Unlimited Bandwidth', available: false },
        { name: '100 GB SSD Storage', available: true },
        { name: 'Unlimited Free SSL', available: true },
        { name: '99.9% Uptime Guarantee', available: true },
        { name: 'Web Application Firewall', available: true }
      ],
      additionalFeatures: [
        { name: 'Daily Backups', available: true },
        { name: 'CDN Integration', available: true },
        { name: 'Priority Support', available: true }
      ]
    },
    {
      name: 'Cloud Startup',
      description: 'Everything needed for your website',
      monthlyPrice: '11.63',
      annualPrice: '9.88',
      renewalText: '$6.99/mo when you renew',
      isPopular: false,
      features: [
        { name: '1 Website', available: true },
        { name: 'Standard Performance', available: true },
        { name: '24/7/365 Support', available: true },
        { name: 'Free Email', available: true },
        { name: 'Unlimited Bandwidth', available: true },
        { name: '100 GB SSD Storage', available: true },
        { name: 'Unlimited Free SSL', available: true },
        { name: '99.9% Uptime Guarantee', available: true },
        { name: 'Web Application Firewall', available: true }
      ],
      additionalFeatures: [
        { name: 'Daily Backups', available: true },
        { name: 'CDN Integration', available: true },
        { name: 'Priority Support', available: true }
      ]
    }
  ]
}));

// Initialize Alpine
window.Alpine = Alpine;

// Initialize stores
document.addEventListener('DOMContentLoaded', () => {
  Alpine.store('theme').init();
  Alpine.store('pwa').init();
});

// Start Alpine
Alpine.start();

// Register Service Worker for PWA - Temporarily disabled
// TODO: Fix Service Worker paths configuration
/*
if ('serviceWorker' in navigator) {
  const updateSW = registerSW({
    onNeedRefresh() {
      Alpine.store('toast').info('New content available, click to refresh!', 10000);
    },
    onOfflineReady() {
      Alpine.store('toast').success('App ready to work offline!');
    },
    onRegistered(r) {
      console.log('SW Registered: ' + r);
    },
    onRegisterError(error) {
      console.log('SW registration error', error);
    }
  });
}
*/

// Add to window for global access
window.toast = Alpine.store('toast');
window.theme = Alpine.store('theme');
window.pwa = Alpine.store('pwa');