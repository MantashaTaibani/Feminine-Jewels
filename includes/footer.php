<footer class="site-footer">
  <div class="container">
    <div class="foot-grid">
      <div>
        <div class="logo">Feminine Jewells</div>
        <p>Elegant, stylish jewellery for every story.</p>
      </div>
      <div><h4>Shop</h4><a href="products.php">All jewellery</a><a href="cart.php">Cart</a></div>
      <div><h4>Company</h4><a href="dashboard.php">My account</a><a href="orders.php">My orders</a></div>
      <div><h4>Visit</h4><p>12 Rose Lane, Bandra West<br>Mumbai 400050<br>+91 98765 43210</p></div>
    </div>
    <div class="foot-bottom">
      <span>© <?= date('Y') ?> Feminine Jewells. All rights reserved.</span>
      <a href="admin/dashboard.php" style="display:inline">Admin panel</a>
    </div>
  </div>
</footer>

<div id="toasts" aria-live="polite"></div>

<script>
  (function () {
    var menuBtn = document.getElementById('menuBtn'), mainNav = document.getElementById('mainNav');
    if (menuBtn) menuBtn.addEventListener('click', function () {
      var open = mainNav.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', open);
    });
    var searchBtn = document.getElementById('searchBtn'), searchBar = document.getElementById('searchBar');
    if (searchBtn) searchBtn.addEventListener('click', function () {
      searchBar.hidden = !searchBar.hidden;
      if (!searchBar.hidden) document.getElementById('hSearch').focus();
    });
  })();
</script>
</body>
</html>
