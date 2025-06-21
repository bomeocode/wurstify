<div class="row g-2">
  <div class="col-12">
    <?php
    $slot = ['name' => 'main', 'data' => $slots['main'] ?? null];
    include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
    ?>
  </div>
</div>