/**
 * Dashboard Component
 * Manages sidebar state and dashboard-specific functionality
 */
export default () => ({
    sidebarExpanded: true,
    mobileMenuOpen: false,

    init() {
        // Load sidebar expanded state from localStorage (desktop only)
        const savedState = localStorage.getItem('dashboard-sidebar-expanded')
        if (savedState !== null) {
            this.sidebarExpanded = savedState === 'true'
        } else {
            // Default to expanded on first visit
            this.sidebarExpanded = true
        }

        // On mobile, sidebar is always collapsed (only shows via drawer)
        this.updateMobileState()

        // Listen for window resize
        window.addEventListener('resize', () => {
            this.updateMobileState()
        })
    },

    updateMobileState() {
        // On mobile (< 1024px), close the mobile menu if it's open
        if (window.innerWidth < 1024) {
            this.mobileMenuOpen = false
        }
    },

    toggleSidebarExpand() {
        // Only works on desktop
        if (window.innerWidth >= 1024) {
            this.sidebarExpanded = !this.sidebarExpanded
            localStorage.setItem('dashboard-sidebar-expanded', this.sidebarExpanded)
        }
    },

    toggleMobileMenu() {
        this.mobileMenuOpen = !this.mobileMenuOpen
    },

    closeMobileMenu() {
        this.mobileMenuOpen = false
    },

    get isMobile() {
        return window.innerWidth < 1024
    }
})
