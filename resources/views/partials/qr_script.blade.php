<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.js"></script>
<script>
// Draws a QR code (as crisp SVG, with a quiet zone) inside the given element.
function makeQr(el, text) {
  if (!el) return;
  try {
    const q = qrcode(0, 'M'); q.addData(text); q.make();
    const n = q.getModuleCount(), z = 2, size = n + 2 * z;
    let d = '';
    for (let r = 0; r < n; r++) for (let c = 0; c < n; c++) if (q.isDark(r, c)) d += 'M' + c + ' ' + r + 'h1v1h-1z';
    el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' + (-z) + ' ' + (-z) + ' ' + size + ' ' + size +
      '" shape-rendering="crispEdges" style="width:100%;height:100%;display:block">' +
      '<rect x="' + (-z) + '" y="' + (-z) + '" width="' + size + '" height="' + size + '" fill="#fff"/>' +
      '<path d="' + d + '" fill="#000"/></svg>';
  } catch (e) { el.textContent = ''; }
}
</script>
