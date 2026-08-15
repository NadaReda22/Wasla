<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  
    document.addEventListener('DOMContentLoaded', () => {
        if(typeof L !== 'undefined') {
            L.Icon.Default.prototype.options.imagePath = "https://unpkg.com/leaflet@1.9.4/dist/images/";
        }
    });
</script>