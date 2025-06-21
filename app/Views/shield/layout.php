<?= $this->include('partials/header') ?>

<div class="container">
    <div class="row h-100 justify-content-center">
        <!-- Die Spalte passt sich an: auf xs 12, sm 8, md 6, lg 4 -->
        <div class="col-12 col-sm-8 col-md-6 col-lg-4">

            <?= $this->renderSection('main') ?>

        </div>
    </div>
</div>

<?= $this->include('partials/footer') ?>