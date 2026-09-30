<?php include('includes/header.php'); ?>
<?php include('includes/navbar.php'); ?>

<section class="hero-section">
    <img src="assets/imgs/hero-bann.webp" alt="" class="hero-banner">

    <div class="hero-overlay"></div>

    <!-- Your content -->
</section>

<!-- Company Introduction -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6  mb-lg-0">
                <div class="pe-lg-5">
                    <span class="text-secondary text-uppercase fw-bold mb-2 d-block text-center">Who We Are</span>
                    <h2 class="mb-4">Crafting Exceptional <br>Resort Experiences</h2>
                    <p class="text-muted">At Stay Vibes Resort, we combine the elegance of premium hospitality with the innovation of a modern fintech investment platform. Our vision is to democratize resort ownership, allowing investors to benefit from the booming luxury tourism industry.</p>
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon me-2 me-sm-3" style="color: var(--secondary-color);"><i class="fas fa-check-circle"></i></div>
                                <h6 class="mb-0 feature-title">Secure Investment</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon me-2 me-sm-3" style="color: var(--secondary-color);"><i class="fas fa-check-circle"></i></div>
                                <h6 class="mb-0 feature-title">High Rental Income</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://images.unsplash.com/photo-1540541338287-41700207dee6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Resort" class="img-fluid rounded-4 shadow-lg" style="max-height: 450px; width: 100%; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="section-padding text-center">
    <div class="container">
        <div class="section-title">
            <span>Process</span>
            <h2>Simple Steps to Start</h2>
        </div>
        <div class="row g-3 g-md-4 mt-2">
            <div class="col-6 col-md-2 mb-3 mb-md-4">
                <div class="step-card">
                    <div class="step-icon mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: var(--bg-light); border-radius: 50%; color: var(--secondary-color); font-size: 1.8rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h6>Step 1</h6>
                    <p class="small text-muted">Register</p>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-3 mb-md-4">
                <div class="step-card">
                    <div class="step-icon mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: var(--bg-light); border-radius: 50%; color: var(--secondary-color); font-size: 1.8rem;">
                        <i class="fas fa-search-dollar"></i>
                    </div>
                    <h6>Step 2</h6>
                    <p class="small text-muted">Choose Plan</p>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-3 mb-md-4">
                <div class="step-card">
                    <div class="step-icon mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: var(--bg-light); border-radius: 50%; color: var(--secondary-color); font-size: 1.8rem;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h6>Step 3</h6>
                    <p class="small text-muted">Submit Proof</p>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-3 mb-md-4">
                <div class="step-card">
                    <div class="step-icon mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: var(--bg-light); border-radius: 50%; color: var(--secondary-color); font-size: 1.8rem;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h6>Step 4</h6>
                    <p class="small text-muted">Verification</p>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3 mb-md-4">
                <div class="step-card" style="background: var(--primary-color); color: white; padding: 25px; border-radius: 20px;">
                    <div class="step-icon mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; color: var(--secondary-color); font-size: 1.5rem;">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h6 class="mb-1" style="color:white">Step 5</h6>
                    <p class="small text-white-50 mb-0">Earn Monthly Income</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Investment Plans Preview -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-title text-center">
            <span>Investment</span>
            <h2>Premium Portfolios</h2>
        </div>
        <div class="row g-4 py-4 justify-content-center">
            <?php
            require_once('includes/connect.php');
            $plans_q = $conn->query("SELECT * FROM plans WHERE status = 1 ORDER BY plan_price ASC LIMIT 3");
            if ($plans_q && $plans_q->num_rows > 0):
                $count = 0;
                while ($plan_db = $plans_q->fetch_assoc()):
                    $count++;
                    $pid = (int)$plan_db['id'];
                    $name = htmlspecialchars($plan_db['plan_name']);
                    $price = "₹" . number_format($plan_db['plan_price']);
                    
                    // Make the middle one featured
                    $is_featured = ($count == 2);
                    
                    // Values from DB using correct column names
                    $rental = $plan_db['yearly_return_percent'] . '%';
                    $referral = $plan_db['referral_percent'] . '%';
                    $stay = $plan_db['free_stay_nights'] . 'N/' . $plan_db['free_stay_days'] . 'D';
                    
                    $ins_val = $plan_db['insurance_amount'];
                    if ($ins_val >= 100000) {
                        $insurance = '₹' . ($ins_val / 100000) . 'L';
                    } else {
                        $insurance = '₹' . number_format($ins_val);
                    }
                    
                    $secure_text = ($plan_db['physical_land'] || strpos($name, 'Plan D') !== false || strpos($name, 'Plan E') !== false) ? 'Secured with Physical Land' : false;
            ?>
            <div class="col-lg-4 col-md-6 px-2 px-sm-3">
                <div class="premium-card plan-card <?php echo $is_featured ? 'plan-card-featured shadow-lg' : 'shadow-sm'; ?> text-center h-100 overflow-hidden">
                    <div class="plan-header">
                        <?php if ($is_featured): ?>
                        <div class="badge bg-secondary mb-3 rounded-pill px-3">MOST POPULAR</div>
                        <?php endif; ?>
                        <h4 class="fw-bold mb-2 <?php echo $is_featured ? 'text-white' : ''; ?>"><?php echo $name; ?></h4>
                        <div class="display-6 fw-bold" style="<?php echo !$is_featured ? 'color: var(--primary-color);' : ''; ?>"><?php echo $price; ?></div>
                    </div>
                    <div class="p-4 pt-0">
                        <?php if ($secure_text): ?>
                            <div class="p-2 mb-3 rounded-3 small fw-bold text-center" style="background: #FFF9E6; color: #856404; border: 1px solid #ffeeba;">
                                <i class="fas fa-map-marked-alt me-1"></i> <?php echo $secure_text; ?>
                            </div>
                        <?php endif; ?>
                        <ul class="list-unstyled feature-list mb-1 text-start">
                            
                            <!-- Always show Hotel Type & Occupancy -->
                            <li><i class="fas fa-hotel text-success me-2"></i> <?php echo htmlspecialchars($plan_db['hotel_type']); ?> (<?php echo $plan_db['adults']; ?>Adults + <?php echo $plan_db['children']; ?>Children)</li>

                            <?php
                            $feats = $conn->query("SELECT * FROM plan_features WHERE plan_id = $pid ORDER BY id ASC");
                            if ($feats && $feats->num_rows > 0):
                                while ($f = $feats->fetch_assoc()):
                                    if ($secure_text && stripos($f['feature_title'], 'land') !== false) continue;
                                    if ($secure_text && stripos($f['feature_title'], 'registry') !== false) continue;
                            ?>
                            <li><i class="fas <?php echo $is_featured ? 'fa-star text-warning' : 'fa-check-circle text-success'; ?> me-2"></i> <?php echo htmlspecialchars($f['feature_title']); ?></li>
                            <?php endwhile; else: ?>
                            <li><i class="fas <?php echo $is_featured ? 'fa-star text-warning' : 'fa-check-circle text-success'; ?> me-2"></i> <?php echo $rental; ?> Annual Rental</li>
                            <li><i class="fas <?php echo $is_featured ? 'fa-star text-warning' : 'fa-check-circle text-success'; ?> me-2"></i> <?php echo $stay; ?> Free Stay</li>
                            <li><i class="fas <?php echo $is_featured ? 'fa-star text-warning' : 'fa-check-circle text-success'; ?> me-2"></i> <?php echo $insurance; ?> Accidental Insurance</li>
                            <?php endif; ?>

                            <!-- Always show Referral Bonus from DB column -->
                            <li><i class="fas <?php echo $is_featured ? 'fa-star text-warning' : 'fa-check-circle text-success'; ?> me-2"></i> <?php echo $referral; ?> Referral Bonus</li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endwhile; endif; ?>
        </div>
        <div class="text-center mt-3">
            <a href="plans.php" class="btn btn-link text-decoration-none fw-bold" style="color: var(--primary-color);">View All Investment Portfolios <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>

<!-- Destinations Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-title text-center">
            <span>Explore</span>
            <h2>Our Destinations</h2>
        </div>
        <div class="row g-4">
            <?php
            $destinations = [
                ['name' => 'Jawai', 'img' => 'assets/imgs/jawai.jpg', 'desc' => 'Experience wild luxury'],
                ['name' => 'Udaipur', 'img' => 'assets/imgs/Udaipur.jpg', 'desc' => 'City of Lakes & Palaces'],
                ['name' => 'Kumbhalgarh', 'img' => 'assets/imgs/kumbhalgarh.jpg', 'desc' => 'Majestic fort views'],
                ['name' => 'Jaishmer', 'img' => 'assets/imgs/Jaishmer.jpg', 'desc' => 'Golden sands & desert camping'],
                ['name' => 'Pushkar', 'img' => 'assets/imgs/Pushkar.jpg', 'desc' => 'Spiritual tranquility'],
                ['name' => 'Goa', 'img' => 'assets/imgs/Goa.png', 'desc' => 'Beachside Luxury Paradises']
            ];
            foreach ($destinations as $dest) {
            ?>
            <style>
                @media (max-width: 767px) {
                    .dest-home-card { height: 250px !important; }
                }
            </style>
            <div class="col-lg-4 col-md-6 col-6">
                <div class="premium-card destination-card dest-home-card position-relative overflow-hidden" style="height: 400px; border-radius: 20px;">
                    <img src="<?php echo $dest['img']; ?>" alt="<?php echo $dest['name']; ?>" class="w-100 h-100 object-fit-cover transition-all" style="transition: transform 0.5s;">
                    <div class="overlay position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                        <h4 class="text-white mb-1"><?php echo $dest['name']; ?></h4>
                        <p class="text-white-50 mb-0"><?php echo $dest['desc']; ?></p>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="text-center mt-3">
            <a href="destinations.php" class="btn btn-gold btn-lg rounded-pill">Check All Destinations</a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-title text-center">
            <span>Benefits</span>
            <h2>Why Choose Stay Vibes</h2>
        </div>
        <div class="row g-2">
            <div class="col-lg-4 col-md-6">
                <div class="premium-card p-4 h-100 text-center">
                    <div class="mb-3 text-secondary" style="font-size: 2.5rem;"><i class="fas fa-shield-alt"></i></div>
                    <h4>Secure Investment</h4>
                    <p class="text-muted">Fully transparent and legally backed investment structures for your peace of mind.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="premium-card p-4 h-100 text-center">
                    <div class="mb-3 text-secondary" style="font-size: 2.5rem;"><i class="fas fa-hotel"></i></div>
                    <h4>Resort Development</h4>
                    <p class="text-muted">Own a part of high-growth resort projects in prime tourism locations.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="premium-card p-4 h-100 text-center">
                    <div class="mb-3 text-secondary" style="font-size: 2.5rem;"><i class="fas fa-crown"></i></div>
                    <h4>Premium Membership</h4>
                    <p class="text-muted">Exclusive access to all our resorts worldwide with VIP benefits.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="premium-card p-4 h-100 text-center">
                    <div class="mb-3 text-secondary" style="font-size: 2.5rem;"><i class="fas fa-chart-line"></i></div>
                    <h4>Monthly Rental Income</h4>
                    <p class="text-muted">Passive income generated from resort operations directly to your account.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="premium-card p-4 h-100 text-center">
                    <div class="mb-3 text-secondary" style="font-size: 2.5rem;"><i class="fas fa-users"></i></div>
                    <h4>Referral Rewards</h4>
                    <p class="text-muted">Earn significant bonuses by inviting partners to join the Stay Vibes family.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="premium-card p-4 h-100 text-center">
                    <div class="mb-3 text-secondary" style="font-size: 2.5rem;"><i class="fas fa-map-marked-alt"></i></div>
                    <h4>Luxury Destinations</h4>
                    <p class="text-muted">Strategically chosen locations that guarantee high demand and appreciation.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Preview -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 text-center text-lg-start">
                <span class="text-secondary text-uppercase fw-bold mb-2 d-block">Questions?</span>
                <h2 class="mb-4">Frequently Asked <br>Questions</h2>
                <p class="text-muted mb-4">Find quick answers to common questions about our investment plans and resort operations.</p>
                <a href="faq.php" class="btn btn-gold">View All FAQs</a>
            </div>
            <div class="col-lg-6">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How safe is my investment with Stay Vibes?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Your investment is backed by real estate assets and managed by STAY VIBES RESORT PRIVATE LIMITED, a registered entity with transparent reporting.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                When do I start receiving rental income?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Rental income begins from the following month after your investment is verified and processed by our admin team.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I visit the resorts I invest in?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Absolutely! Depending on your chosen plan, you are entitled to a specific number of complimentary nights at our luxury destinations every year.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<section class="text-white" style="background: linear-gradient(to bottom, rgba(11, 44, 77, 0.9), rgba(11, 44, 77, 0.9)); padding: 40px 0 30px 0; margin-bottom:10px">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-4" style="color: var(--secondary-color);">Ready to Start Your <br>Luxury Investment Journey?</h2>
                <p class="lead mb-5" style="color:white">Join hundreds of satisfied investors who are building wealth through premium hospitality.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="contact.php" class="btn btn-gold btn-lg px-5 rounded-pill">Contact Now</a>
                    <a href="register.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">Join Today</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Connect the CTA section directly to the footer by removing the footer's default top margin */
    .footer {
        margin-top: 0 !important;
    }
</style>

<?php include('includes/footer.php'); ?>
