<?php 
$is_subpage = false;
include('includes/header.php'); 
?>
<?php include('includes/navbar.php'); ?>

<!-- Internal Hero -->
<section class="section-padding bg-primary text-white" style="background: linear-gradient(rgba(11, 44, 77, 0.9), rgba(11, 44, 77, 0.9)), url('assets/imgs/hero_banner.jpg'); background-size: cover; background-position: center;">
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
                ['name' => 'Jawai', 'status' => 'Upcoming', 'img' => 'assets/imgs/jawai.jpg', 'desc' => 'Experience the wild luxury and leopard safaris at our upcoming resort.'],
                ['name' => 'Udaipur', 'status' => 'Operational', 'img' => 'assets/imgs/Udaipur.jpg', 'desc' => 'Experience the royalty of the lake city at our heritage resort.'],
                ['name' => 'Kumbhalgarh', 'status' => 'Under Development', 'img' => 'assets/imgs/kumbhalgarh.jpg', 'desc' => 'Majestic fort views meet luxury living in the Aravalli hills.'],
                ['name' => 'Jaisalmer', 'status' => 'Upcoming', 'img' => 'assets/imgs/Jaishmer.jpg', 'desc' => 'Discover the golden sands with our premium desert camping and luxury stays.'],
                ['name' => 'Pushkar', 'status' => 'Operational', 'img' => 'assets/imgs/Pushkar.jpg', 'desc' => 'Boutique desert resort offering spiritual tranquility and modern luxury.'],
                ['name' => 'Goa', 'status' => 'Operational', 'img' => 'assets/imgs/Goa.png', 'desc' => 'Luxury beach villas with private access to the Arabian Sea.'],
                ['name' => 'Mahabaleshwar', 'status' => 'Upcoming', 'img' => 'assets/imgs/mahabalesh.jpg', 'desc' => 'Cool breezes and strawberry fields await at our serene hill station resort.'],
                ['name' => 'Lonavala', 'status' => 'Upcoming', 'img' => 'assets/imgs/Lonawala.jpg', 'desc' => 'Lush green valleys and misty mountain views for a perfect weekend getaway.'],
                ['name' => 'Deo Daman', 'status' => 'Upcoming', 'img' => 'assets/imgs/Daman.jpg', 'desc' => 'Coastal charm and historical elegance combined in our seaside property.'],
                ['name' => 'Girnar', 'status' => 'Under Development', 'img' => 'assets/imgs/Girnar.jpg', 'desc' => 'Spiritual heights and nature retreats at the foothills of Girnar.'],
                ['name' => 'Ahmedabad', 'status' => 'Upcoming', 'img' => 'assets/imgs/Ahmedabad.jpg', 'desc' => 'Modern luxury in the heart of India\'s first UNESCO World Heritage City.'],
                ['name' => 'Rishikesh', 'status' => 'Upcoming', 'img' => 'assets/imgs/Rishikesh.jpg', 'desc' => 'Yoga and wellness sanctuary on the banks of the Holy Ganges.'],
                ['name' => 'Shimla', 'status' => 'Under Development', 'img' => 'assets/imgs/Shimla.jpg', 'desc' => 'High-altitude luxury retreat nestled in the Himalayan pine forests.'],
                ['name' => 'Ujjain', 'status' => 'Upcoming', 'img' => 'assets/imgs/Ujjain.png', 'desc' => 'Divine experiences meet premium comfort near the Mahakaleshwar temple.'],
                ['name' => 'Somnath', 'status' => 'Operational', 'img' => 'assets/imgs/Somnath.jpg', 'desc' => 'Coastal divinity meets premium comfort at our temple-view resort.']
            ];
            ?>
            <style>
                @media (max-width: 767px) {
                    .dest-page-img { height: 160px !important; }
                }
            </style>
            <?php
            foreach ($destinations as $dest) {
            ?>
            <div class="col-lg-4 col-md-6 col-6">
                <div class="premium-card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
                    <div class="position-relative">
                        <img src="<?php echo $dest['img']; ?>" alt="<?php echo $dest['name']; ?>" class="img-fluid dest-page-img" style="height: 250px; width: 100%; object-fit: cover;">
                        <span class="badge position-absolute top-0 end-0 m-3 <?php echo ($dest['status'] == 'Operational' ? 'bg-success' : ($dest['status'] == 'Upcoming' ? 'bg-info' : 'bg-warning')); ?>">
                            <?php echo $dest['status']; ?>
                        </span>
                    </div>
                    <div class="p-2 p-md-3 bg-white">
                        <h5 class="fw-bold mb-1" style="font-size: 1.1rem;"><?php echo $dest['name']; ?></h5>
                        <p class="text-muted small mb-1" style="font-size: 0.8rem; line-height: 1.4;"><?php echo $dest['desc']; ?></p>
                        <hr class="opacity-10" style="margin-top: 0px;margin-bottom: 0.25rem !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary" style="font-size: 11px;"><i class="fas fa-star me-1 text-warning"></i> 4.9/5 Rating</span>
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

<?php include('includes/footer.php'); ?>
