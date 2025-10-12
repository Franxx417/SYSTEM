import './bootstrap';

// Ensure jQuery is available globally for plugins that expect window.$
import $ from 'jquery';
window.$ = $;
window.jQuery = $;

// Bootstrap is loaded via CDN in the layout file for faster loading
// jQuery UI is loaded via CDN in the layout file

// Page-specific scripts are loaded directly in templates to avoid conflicts