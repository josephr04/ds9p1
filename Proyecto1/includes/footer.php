<?php
// footer.php - SellFlow | DS9P1
$current_year = date('Y');

$apiBase = "http://127.0.0.1:8000/api";
$responseCategorias = @file_get_contents("$apiBase/categorias");
$categorias = $responseCategorias ? json_decode($responseCategorias, true) : [];

$rol = $_SESSION['empleado']['rol'] ?? null;
$esAdminOEmpleado = in_array($rol, [1, 2]);
?>

<footer class="sf-footer">

  <?php if (!$esAdminOEmpleado): ?>
  <div class="sf-footer__divider"></div>

  <div class="sf-footer__main">
    <div class="sf-footer__container">
      <div class="sf-footer__grid">

        <!-- Brand column -->
        <div class="sf-footer__col sf-footer__col--brand">
          <a href="store.php" class="sf-footer__logo">SellFlow</a>
          <p class="sf-footer__tagline">
            Equipamiento de vanguardia diseñado para los entusiastas de la tecnología.
          </p>
          <div class="sf-footer__contact-info">
            <span class="sf-footer__contact-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              Panamá, Panamá
            </span>
            <span class="sf-footer__contact-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              contacto@sellflow.com
            </span>
          </div>
        </div>

        <!-- Categorías -->
        <div class="sf-footer__col">
          <h4 class="sf-footer__col-title">Categorías</h4>
          <ul class="sf-footer__link-list">
            <li>
              <a href="store.php?cat=0" class="sf-footer__link">Todos los Productos</a>
            </li>
            <?php foreach ($categorias as $c): ?>
              <li>
                <a href="store.php?cat=<?= (int)$c['idCategoria'] ?>" class="sf-footer__link">
                  <?= htmlspecialchars($c['nombreCat']) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Mi Cuenta + Pagos -->
        <div class="sf-footer__col">
          <h4 class="sf-footer__col-title">Mi Cuenta</h4>
          <ul class="sf-footer__link-list">
            <li><a href="login.php" class="sf-footer__link">Iniciar Sesión</a></li>
          </ul>

          <h4 class="sf-footer__col-title sf-footer__col-title--spaced">Pagos aceptados</h4>
          <div class="sf-footer__payments">
            <span class="sf-footer__payment-badge">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              Débito
            </span>
            <span class="sf-footer__payment-badge">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              Crédito
            </span>
          </div>
        </div>

      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="sf-footer__bottom">
    <div class="sf-footer__container">
      <div class="sf-footer__bottom-inner">
        <p class="sf-footer__copyright">
          &copy; <?= $current_year ?> SellFlow. Todos los derechos reservados.
        </p>
      </div>
    </div>
  </div>

</footer>

<style>
.sf-footer {
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  color: #374151;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  margin-top: 4rem;
}

.sf-footer__divider {
  height: 3px;
  background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 50%, #3b82f6 100%);
}

.sf-footer__container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.sf-footer__main {
  padding: 3rem 0 2.5rem;
}

.sf-footer__grid {
  display: grid;
  grid-template-columns: 2fr 1.4fr 1.4fr;
  gap: 3rem;
}

.sf-footer__logo {
  display: inline-block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #3b82f6;
  text-decoration: none;
  letter-spacing: -0.02em;
  margin-bottom: 0.75rem;
}

.sf-footer__tagline {
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.6;
  margin: 0 0 1.25rem;
  max-width: 240px;
}

.sf-footer__contact-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.sf-footer__contact-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  color: #6b7280;
}

.sf-footer__contact-item svg {
  flex-shrink: 0;
  color: #9ca3af;
}

.sf-footer__col-title {
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #111827;
  margin: 0 0 0.875rem;
}

.sf-footer__col-title--spaced {
  margin-top: 1.75rem;
}

.sf-footer__link-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.sf-footer__link {
  font-size: 0.875rem;
  color: #6b7280;
  text-decoration: none;
  transition: color 0.15s, padding-left 0.15s;
  display: inline-block;
}

.sf-footer__link:hover {
  color: #3b82f6;
  padding-left: 0.3rem;
}

.sf-footer__payments {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.sf-footer__payment-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.3rem 0.7rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  color: #374151;
  background: #f9fafb;
}

.sf-footer__bottom {
  border-top: 1px solid #e5e7eb;
  padding: 1.25rem 0;
  background: #f9fafb;
}

.sf-footer__bottom-inner {
  display: flex;
  align-items: center;
  justify-content: center;
}

.sf-footer__copyright {
  margin: 0;
  font-size: 0.8125rem;
  color: #9ca3af;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
  justify-content: center;
}

@media (max-width: 768px) {
  .sf-footer__grid {
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
  }
  .sf-footer__col--brand {
    grid-column: 1 / -1;
  }
}

@media (max-width: 480px) {
  .sf-footer__grid {
    grid-template-columns: 1fr;
  }
}
</style>