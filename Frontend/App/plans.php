<?php 
$is_subpage = true;
include('../includes/header.php'); 
require_auth('../pages/login.php');
include('../includes/connect.php');
?>
<?php include('include/navbar.php'); ?>

<section class="section-padding bg-light" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="dashboard-header mb-4 text-center">
                    <h2 class="fw-bold mb-1">Investment Portfolios</h2>
                    <p class="text-muted small mb-0">Select an investment portfolio to activate your account and start earning.</p>
                </div>

                <div class="row justify-content-center g-3 mt-2">
                    <?php
                    $query = "SELECT * FROM plans WHERE status = 1 ORDER BY plan_price ASC";
                    $result = $conn->query($query);
                    
                    if ($result && $result->num_rows > 0):
                        while ($plan_db = $result->fetch_assoc()):
                            $name = $plan_db['plan_name'];
                            $price = '₹' . number_format($plan_db['plan_price']);
                            $stay = $plan_db['free_stay_nights'] . ' Night' . ($plan_db['free_stay_nights'] > 1 ? 's' : '') . ' / ' . $plan_db['free_stay_days'] . ' Day' . ($plan_db['free_stay_days'] > 1 ? 's' : '');
                            
                            $is_premium = (strpos($name, 'Plan D') !== false || strpos($name, 'Plan E') !== false);
                            $color = $is_premium ? 'var(--secondary-color)' : 'var(--primary-color)';
                            $secure_text = ($plan_db['physical_land'] || $is_premium) ? 'Secured with Physical Land (Plot Registry)' : false;
                    ?>
                    <div class="col-md-6 col-lg-4 px-2 px-sm-3 mb-3">
                        <div class="premium-card h-100 border-0 shadow-sm position-relative overflow-hidden bg-white text-center" style="border-top: 5px solid <?php echo $color; ?> !important; border-radius: 24px; transition: transform 0.3s ease;">
                            <?php if($plan_db['physical_land'] || $is_premium) { ?>
                                <span class="badge position-absolute top-0 end-0 m-3 px-3 py-1.5 fw-bold" style="background-color: var(--secondary-color); color: #ffffff; font-size: 0.65rem; letter-spacing: 0.5px; border-radius: 50px; z-index: 2;">SECURED ASSET</span>
                            <?php } ?>
                            
                            <div class="p-4 py-5">
                                <h4 class="fw-bold mb-1 text-dark" style="font-size: 1.15rem;"><?php echo htmlspecialchars($name); ?></h4>
                                <div class="display-6 fw-extrabold mb-4" style="color: <?php echo $color; ?>; font-weight: 800; font-size: 1.8rem;"><?php echo $price; ?></div>
                                
                                <?php if($secure_text) { ?>
                                    <div class="p-2 mb-4 rounded-3 small fw-bold text-center" style="background: #FFF9E6; color: #856404; font-size: 0.72rem; border: 1px solid rgba(201, 162, 39, 0.15);">
                                        <i class="fas fa-map-marked-alt me-1.5"></i> <?php echo $secure_text; ?>
                                    </div>
                                <?php } ?>
                                
                                <div class="p-3 bg-light rounded-4 mb-4" style="border: 1px dashed rgba(0,0,0,0.06);">
                                    <div class="icon-sm text-primary mb-2 mx-auto" style="font-size: 1.25rem;"><i class="fas fa-bed"></i></div>
                                    <h6 class="mb-1 fw-bold text-dark" style="font-size: 0.8rem; letter-spacing: 0.5px;">Free Stay Benefit</h6>
                                    <p class="small text-muted mb-0 fw-semibold" style="font-size: 0.8rem;"><?php echo $stay; ?></p>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="payment.php?plan=<?php echo urlencode($name); ?>" class="btn btn-gold btn-sm px-4 py-2.5 fw-bold rounded-pill shadow-sm text-uppercase w-100" style="font-size: 12px; background-color: <?php echo $color; ?>; border-color: <?php echo $color; ?>; color: white;">
                                        Invest Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="col-12">
                        <div class="premium-card p-5 bg-white border-0 shadow-sm text-center" style="border-radius: 24px;">
                            <p class="text-muted mb-0">No active investment plans available at the moment.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</section>

<?php include('include/footer.php'); ?>
