<?php 
$is_subpage = true;
include('../includes/header.php'); 
?>
<?php include('../includes/navbar.php'); ?>

<!-- Internal Hero -->
<section class="section-padding bg-primary text-white pt-5 mt-5" style="background: linear-gradient(rgba(11, 44, 77, 0.9), rgba(11, 44, 77, 0.9)), url('https://images.unsplash.com/photo-1551882547-ff43c63faf76?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center;">
    <div class="container pt-5 text-center">
        <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 3px; color: var(--secondary-color);">Exclusive Opportunities</h6>
        <h1 class="display-4 fw-bold mb-4" style="color: var(--secondary-color);">Investment Portfolios</h1>
        <p class="lead mx-auto" style="max-width: 800px;">Secure your future with real estate backed assets. Premium rental income, accidental insurance, and lifetime luxury benefits.</p>
    </div>
</section>

<!-- Plans Section -->
<section class="section-padding">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <?php
            require_once('../includes/connect.php');
            $query = "SELECT * FROM plans WHERE status = 1 ORDER BY plan_price ASC";
            $result = $conn->query($query);
            
            while ($plan_db = $result->fetch_assoc()) {
                $name = $plan_db['plan_name'];
                $is_premium = (strpos($name, 'Plan D') !== false || strpos($name, 'Plan E') !== false);
                $color = $is_premium ? 'var(--secondary-color)' : 'var(--primary-color)';
                $col_class = $is_premium ? 'col-lg-5' : 'col-lg-4';
                
                // Formatting values
                $price = '₹' . number_format($plan_db['plan_price']);
                $rental = $plan_db['yearly_return_percent'] . '%';
                $referral = $plan_db['referral_percent'] . '%';
                $stay = $plan_db['free_stay_nights'] . ' Night' . ($plan_db['free_stay_nights'] > 1 ? 's' : '') . ' / ' . $plan_db['free_stay_days'] . ' Day' . ($plan_db['free_stay_days'] > 1 ? 's' : '');
                
                $ins_val = $plan_db['insurance_amount'];
                if ($ins_val >= 100000) {
                    $insurance = '₹' . ($ins_val / 100000) . ' Lakh';
                } else {
                    $insurance = '₹' . number_format($ins_val);
                }

                $secure_text = $plan_db['physical_land'] ? 'Secured with Physical Land (Plot Registry)' : false;
            ?>
            <div class="<?php echo $col_class; ?> col-md-6 mb-4">
                <div class="premium-card h-100 border-0 shadow-lg position-relative overflow-hidden" style="background: white; border-top: 5px solid <?php echo $color; ?> !important;">
                    <?php if($plan_db['physical_land']) { ?>
                        <div class="badge bg-gold position-absolute top-0 end-0 m-3 px-3 py-2" style="background-color: var(--secondary-color); font-size: 0.7rem; letter-spacing: 1px;">SECURED ASSET</div>
                    <?php } ?>
                    
                    <div class="p-5">
                        <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($name); ?></h3>
                        <div class="display-5 fw-bold mb-4" style="color: <?php echo $color; ?>;"><?php echo $price; ?></div>
                        
                        <?php if($secure_text) { ?>
                            <div class="p-3 mb-4 rounded-3 border-start border-4 small fw-bold" style="background: #FFF9E6; border-left-color: var(--secondary-color) !important; color: #856404;">
                                <i class="fas fa-map-marked-alt me-2"></i> <?php echo $secure_text; ?>
                            </div>
                        <?php } ?>

                        <ul class="list-unstyled mb-5">
                            <li class="mb-3 d-flex align-items-start">
                                <div class="icon-sm me-3 mt-1" style="color: <?php echo $color; ?>;"><i class="fas fa-bed"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Free Stay: <?php echo $stay; ?></h6>
                                    <p class="small text-muted mb-0"><?php echo htmlspecialchars($plan_db['hotel_type']); ?> | <?php echo $plan_db['adults']; ?> Adults + <?php echo $plan_db['children']; ?> Child</p>
                                </div>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <div class="icon-sm me-3 mt-1" style="color: <?php echo $color; ?>;"><i class="fas fa-percentage"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo $rental; ?> Rental Income / Year</h6>
                                    <p class="small text-muted mb-0">Monthly credited to your wallet</p>
                                </div>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <div class="icon-sm me-3 mt-1" style="color: <?php echo $color; ?>;"><i class="fas fa-shield-alt"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo $insurance; ?> Accidental Insurance</h6>
                                    <p class="small text-muted mb-0">Comprehensive policy cover included</p>
                                </div>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <div class="icon-sm me-3 mt-1" style="color: <?php echo $color; ?>;"><i class="fas fa-users"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo $referral; ?> Referral Bonus</h6>
                                    <p class="small text-muted mb-0">Earn on every partner joined</p>
                                </div>
                            </li>
                            <li class="mb-0 d-flex align-items-start">
                                <div class="icon-sm me-3 mt-1" style="color: <?php echo $color; ?>;"><i class="fas fa-id-card"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Stay Vibes Membership Card</h6>
                                    <p class="small text-muted mb-0"><?php echo $plan_db['membership_years']; ?> Year Free Stay after <?php echo $plan_db['lockin_years']; ?>-Year Lock-in</p>
                                </div>
                            </li>
                        </ul>
                        
                        <a href="payment.php?plan=<?php echo urlencode($name); ?>" class="btn w-100 py-3 fw-bold text-uppercase" style="background-color: <?php echo $color; ?>; color: white; border-radius: 12px; letter-spacing: 1px;">Invest Now</a>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>

<!-- Investment Payment Structures -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-title text-center">
            <span style="color: var(--secondary-color); text-transform: uppercase; letter-spacing: 2px; font-weight: 700; font-size: 0.8rem;">Payment Schedules</span>
            <h2 class="fw-bold mb-4">Investment Installment Plans</h2>
            <p class="text-muted mx-auto" style="max-width: 700px;">Choose your convenient payment structure based on your portfolio size. Clear, transparent, and easy installments.</p>
        </div>

        <div class="row g-4 mt-4">
            <!-- Table 1: Plan A Structure -->
            <div class="col-lg-6">
                <div class="premium-card p-4 bg-white border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box me-3 text-primary" style="font-size: 2rem;"><i class="fas fa-layer-group"></i></div>
                        <div>
                            <h4 class="mb-0 fw-bold">Plan Structure A</h4>
                            <span class="badge bg-primary px-3 rounded-pill mt-1">40% - 30% - 30%</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center small mb-0">
                            <thead class="bg-primary text-white border-0">
                                <tr>
                                    <th class="py-3 border-0">Total Investment</th>
                                    <th class="py-3 border-0">1st (40%)</th>
                                    <th class="py-3 border-0">2nd (30%)</th>
                                    <th class="py-3 border-0">3rd (30%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold py-3">₹21,000</td>
                                    <td>₹8,400</td>
                                    <td>₹6,300</td>
                                    <td>₹6,300</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold py-3">₹39,999</td>
                                    <td>₹16,000</td>
                                    <td>₹12,000</td>
                                    <td>₹12,000</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold py-3">₹79,999</td>
                                    <td>₹32,000</td>
                                    <td>₹24,000</td>
                                    <td>₹24,000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-4 bg-light p-3 rounded-3 mb-0"><i class="fas fa-info-circle me-2"></i> For these plans, the total amount is split into 3 parts. Example: For a 21,000 plan, pay 8,400 first, then 6,300, and 6,300 finally.</p>
                </div>
            </div>

            <!-- Table 2: Plan B Structure -->
            <div class="col-lg-6">
                <div class="premium-card p-4 bg-white border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box me-3 text-secondary" style="font-size: 2rem;"><i class="fas fa-gem"></i></div>
                        <div>
                            <h4 class="mb-0 fw-bold">Plan Structure B (Premium)</h4>
                            <span class="badge bg-gold text-dark px-3 rounded-pill mt-1" style="background-color: var(--secondary-color);">20% - 20% - 60%</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center small mb-0">
                            <thead class="bg-secondary text-white border-0" style="background-color: var(--secondary-color) !important;">
                                <tr>
                                    <th class="py-3 border-0">Total Investment</th>
                                    <th class="py-3 border-0">1st (20%)</th>
                                    <th class="py-3 border-0">2nd (20%)</th>
                                    <th class="py-3 border-0">3rd (60%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold py-3">₹6,25,000</td>
                                    <td>₹1,25,000</td>
                                    <td>₹1,25,000</td>
                                    <td>₹3,75,000</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold py-3">₹12,50,000</td>
                                    <td>₹2,50,000</td>
                                    <td>₹2,50,000</td>
                                    <td>₹7,50,000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-4 bg-light p-3 rounded-3 mb-0"><i class="fas fa-info-circle me-2"></i> This structure is for high-value investments (6.25L and 12.5L). Pay 20% in the first two installments and 60% as the final part.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Detailed Rules & Conditions -->
<section class="section-padding">
    <div class="container">
        <div class="section-title text-center">
            <span style="color: var(--secondary-color); text-transform: uppercase; letter-spacing: 2px; font-weight: 700; font-size: 0.8rem;">Rules & Guidelines</span>
            <h2 class="fw-bold">Investment Policy (Terms & Conditions)</h2>
        </div>

        <div class="row g-4 justify-content-center mt-4">
            <!-- Policy Block 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="premium-card p-4 h-100 border-0 shadow-sm transition-hover" style="border-radius: 20px;">
                    <div class="icon-box-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">1</div>
                    <h5 class="fw-bold mb-3">Installment 1 & 2 Details</h5>
                    <p class="small text-muted mb-3"><strong class="text-dark">1st Installment:</strong> To be paid at the time of taking membership. Within 1 month, you will receive your Membership Card and official rules/offers via postal mail.</p>
                    <p class="small text-muted mb-3"><strong class="text-dark">2nd Installment:</strong> To be paid within 40 to 45 days of the start of your membership.</p>
                    <div class="p-3 rounded-3 small fw-bold" style="background: rgba(201, 162, 39, 0.1); color: var(--secondary-color);">
                        <i class="fas fa-calendar-check me-2"></i> Hotel booking facility starts 61 days after the second installment is paid.
                    </div>
                </div>
            </div>

            <!-- Policy Block 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="premium-card p-4 h-100 border-0 shadow-sm transition-hover" style="border-radius: 20px;">
                    <div class="icon-box-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">2</div>
                    <h5 class="fw-bold mb-3">Installment 3 & Rental Income</h5>
                    <p class="small text-muted mb-3"><strong class="text-dark">3rd Installment:</strong> The final payment must be made within 70 to 75 days of receiving your Membership Card.</p>
                    <p class="small text-muted mb-3"><strong class="text-dark">Rental Income:</strong> 91 days after full payment is received, investors start earning 1.25% to 1.50% annual Rental Income.</p>
                    <div class="p-3 rounded-3 small fw-bold" style="background: rgba(11, 44, 77, 0.1); color: var(--primary-color);">
                        <i class="fas fa-hand-holding-usd me-2"></i> Earnings are credited monthly to your secure wallet.
                    </div>
                </div>
            </div>

            <!-- Policy Block 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="premium-card p-4 h-100 border-0 shadow-sm transition-hover" style="border-radius: 20px; border-top: 5px solid #dc3545 !important;">
                    <div class="icon-box-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;"><i class="fas fa-exclamation-triangle"></i></div>
                    <h5 class="fw-bold mb-3 text-danger">Payment Default Policy</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-start small text-muted">
                            <i class="fas fa-times-circle text-danger me-2 mt-1"></i>
                            <span><strong>1st Installment Only:</strong> No profits or stay benefits. Funds returned after 36 months after TDS deduction.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start small text-muted">
                            <i class="fas fa-exclamation-circle text-warning me-2 mt-1"></i>
                            <span><strong>2 Installments Only:</strong> 3 years of hotel stay facility provided, but no rental income profit.</span>
                        </li>
                        <li class="mb-0 d-flex align-items-start small text-muted">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <span><strong>Full Payment:</strong> All benefits (Rental + Stay) provided exactly as per the chosen plan.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-5 p-4 rounded-4 text-center" style="background: rgba(11, 44, 77, 0.05);">
            <p class="mb-0 text-muted small"><i class="fas fa-gavel me-2"></i> <strong>Legal Disclaimer:</strong> Investment carries risk. All disputes are subject to Rajasthan Jurisdiction. Please read the official physical rulebook carefully.</p>
        </div>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
