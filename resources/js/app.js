import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Ensure jQuery is available globally for plugins that expect window.$
import $ from 'jquery';
window.$ = $;
window.jQuery = $;

// If available via node_modules, uncomment the next line; otherwise we load via CDN on the page
// import 'jquery-ui-dist/jquery-ui';

// Page-specific scripts are loaded directly in templates to avoid conflicts