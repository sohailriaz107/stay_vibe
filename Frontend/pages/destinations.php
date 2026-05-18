<?php 
$is_subpage = true;
include('../includes/header.php'); 
?>
<?php include('../includes/navbar.php'); ?>

<!-- Internal Hero -->
<section class="section-padding bg-primary text-white pt-5 mt-5" style="background: linear-gradient(rgba(11, 44, 77, 0.9), rgba(11, 44, 77, 0.9)), url('../assets/imgs/hero_banner.jpg'); background-size: cover; background-position: center;">
    <div class="container pt-5 text-center">
        <h1 class="display-4 fw-bold" style="color: var(--secondary-color);">Our Destinations</h1>
        <p class="lead">Explore the luxury resorts where your investments grow.</p>
    </div>
</section>

<!-- Destinations Gallery -->
<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <?php
            $destinations = [
                ['name' => 'Udaipur', 'status' => 'Operational', 'img' => '../assets/imgs/Udaipur.jpg', 'desc' => 'Experience the royalty of the lake city at our heritage resort.'],
                ['name' => 'Goa', 'status' => 'Operational', 'img' => '../assets/imgs/Goa.png', 'desc' => 'Luxury beach villas with private access to the Arabian Sea.'],
                ['name' => 'Shimla', 'status' => 'Under Development', 'img' => '../assets/imgs/Shimla.jpg', 'desc' => 'High-altitude luxury retreat nestled in the Himalayan pine forests.'],
                ['name' => 'Pushkar', 'status' => 'Operational', 'img' => '../assets/imgs/Pushkar.jpg', 'desc' => 'Boutique desert resort offering spiritual tranquility and modern luxury.'],
                ['name' => 'Rishikesh', 'status' => 'Upcoming', 'img' => '../assets/imgs/Rishikesh.jpg', 'desc' => 'Yoga and wellness sanctuary on the banks of the Holy Ganges.'],
                ['name' => 'Somnath', 'status' => 'Operational', 'img' => '../assets/imgs/Somnath.jpg', 'desc' => 'Coastal divinity meets premium comfort at our temple-view resort.']
            ];
            foreach ($destinations as $dest) {
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="premium-card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
                    <div class="position-relative">
                        <img src="<?php echo $dest['img']; ?>" alt="<?php echo $dest['name']; ?>" class="img-fluid" style="height: 250px; width: 100%; object-fit: cover;">
                        <span class="badge position-absolute top-0 end-0 m-3 <?php echo ($dest['status'] == 'Operational' ? 'bg-success' : ($dest['status'] == 'Upcoming' ? 'bg-info' : 'bg-warning')); ?>">
                            <?php echo $dest['status']; ?>
                        </span>
                    </div>
                    <div class="p-4 bg-white">
                        <h4 class="fw-bold"><?php echo $dest['name']; ?></h4>
                        <p class="text-muted small"><?php echo $dest['desc']; ?></p>
                        <hr class="opacity-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary fw-bold small"><i class="fas fa-star me-1"></i> 4.9/5 Rating</span>
                            
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>

<!-- Future Development Call -->
<section class="section-padding bg-light text-center">
    <div class="container">
        <h2 class="mb-4">Want to Suggest a Destination?</h2>
        <p class="text-muted mb-5 mx-auto" style="max-width: 600px;">We are constantly looking for prime locations to expand our portfolio. Partner with us for development in your city.</p>
        <a href="contact.php" class="btn btn-gold btn-lg rounded-pill px-5">Partner With Us</a>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
