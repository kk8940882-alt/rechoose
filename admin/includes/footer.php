    </div><!-- /page-body -->
</div><!-- /main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
// Auto dismiss alerts
document.querySelectorAll('.alert-auto').forEach(el => {
    setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),400); }, 4000);
    el.style.transition = 'opacity 0.4s';
});
// Confirm delete
function confirmDelete(form, msg) {
    if (confirm(msg || 'Are you sure you want to delete this item? This cannot be undone.')) {
        form.submit();
    }
    return false;
}
</script>
</body>
</html>
