<?php 
$is_subpage = false;
include('includes/header.php'); 
?>
<?php include('includes/navbar.php'); ?>

<!-- Internal Hero -->
<section class="section-padding bg-primary text-white" style="background: linear-gradient(rgba(11, 44, 77, 0.9), rgba(11, 44, 77, 0.9)), url('https://images.unsplash.com/photo-1551882547-ff43c63faf76?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center;">
    <div class="container text-center">
        <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 3px; color: var(--secondary-color);">Exclusive Opportunities</h6>
        <h1 class="display-4 fw-bold mb-4" style="color: var(--secondary-color);">Investment Portfolios</h1>
        <p class="lead mx-auto" style="max-width: 800px;">Secure your future with real estate backed assets. Premium rental income, accidental insurance, and lifetime luxury benefits.</p>
    </div>
</section>

<!-- Plans Section -->
<section class="section-padding">
    <div class="container">
        <div class="row gx-2 gx-sm-3 gy-3 gy-sm-4 justify-content-center">
            <?php
            require_once('includes/connect.php');
            $query = "SELECT * FROM plans WHERE status = 1 ORDER BY plan_price ASC";
            $result = $conn->query($query);
            
            while ($plan_db = $result->fetch_assoc()) {
                $name = $plan_db['plan_name'];
                $is_premium = (strpos($name, 'Plan D') !== false || strpos($name, 'Plan E') !== false || strpos($name, 'Plan F') !== false);
                $color = $is_premium ? 'var(--secondary-color)' : 'var(--primary-color)';
                
                // Set explicitly to col-lg-4 so all plans fit 3 per row (A,B,C on row 1, D,E,F on row 2)
                $col_class = 'col-lg-4';
                
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

                $secure_text = ($plan_db['physical_land'] || strpos($name, 'Plan D') !== false || strpos($name, 'Plan E') !== false || strpos($name, 'Plan F') !== false) ? 'Secured with Physical Land' : false;
            ?>
            <div class="<?php echo $col_class; ?> col-md-6 px-2 px-sm-3">
                <div class="premium-card h-100 border-0 shadow-sm position-relative overflow-hidden" style="background: white; border-top: 5px solid <?php echo $color; ?> !important; border-radius: 24px;">
                    <?php if($plan_db['physical_land'] || strpos($name, 'Plan D') !== false || strpos($name, 'Plan E') !== false || strpos($name, 'Plan F') !== false) { ?>
                        <div class="badge bg-gold position-absolute top-0 end-0 m-3 px-3 py-2" style="background-color: var(--secondary-color); font-size: 0.7rem; letter-spacing: 1px;">SECURED ASSET</div>
                    <?php } ?>
                    
                    <div class="p-3 p-sm-5">
                        <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($name); ?></h3>
                        <div class="display-5 fw-bold mb-4" style="color: <?php echo $color; ?>;"><?php echo $price; ?></div>
                        
                        <?php if($secure_text) { ?>
                            <div class="p-3 mb-4 rounded-3 border-start border-4 small fw-bold" style="background: #FFF9E6; border-left-color: var(--secondary-color) !important; color: #856404;">
                                <i class="fas fa-map-marked-alt me-2"></i> <?php echo $secure_text; ?>
                            </div>
                        <?php } ?>

                        <ul class="list-unstyled ">
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
                            <li class="mb-3 d-flex align-items-start">
                                <div class="icon-sm me-3 mt-1" style="color: <?php echo $color; ?>;"><i class="fas fa-id-card"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Stay Vibes Membership Card</h6>
                                    <p class="small text-muted mb-0"><?php echo $plan_db['membership_years']; ?> Year Free Stay after <?php echo $plan_db['lockin_years']; ?>-Year Lock-in</p>
                                </div>
                            </li>

                            <?php
                            // Fetch dynamic features from plan_features table
                            $pid = (int)$plan_db['id'];
                            $feats = $conn->query("SELECT * FROM plan_features WHERE plan_id = $pid ORDER BY id ASC");
                            $dynamic_features = [];
                            if ($feats && $feats->num_rows > 0):
                                while ($f = $feats->fetch_assoc()):
                                    $ftitle = $f['feature_title'];
                                    
                                    // 1. FILTER DUPLICATES: Skip features that are already rendered by the native DB columns above
                                    if (stripos($ftitle, 'Accidental insurance') !== false) continue;
                                    if (stripos($ftitle, 'Rental Income') !== false) continue;
                                    // We allow 'Monthly Wallet Income' to show up so we removed it from filters
                                    if (stripos($ftitle, 'Referral') !== false) continue; // Broadened to catch 'Referral Income' and 'Referral Bonus'
                                    if (stripos($ftitle, 'Membership Card') !== false) continue;
                                    if (stripos($ftitle, 'Free Stay') !== false && stripos($ftitle, 'Night') !== false) continue;
                                    if (stripos($ftitle, 'Lock-in') !== false) continue; // Catch '5 Year Free Stay After 3 Year Lock-in'
                                    if ($secure_text && stripos($ftitle, 'land') !== false) continue;
                                    if ($secure_text && stripos($ftitle, 'registry') !== false) continue;

                                    // 2. FORMATTING: Give a nice heading and subtext
                                    $icon = "fa-check-circle";
                                    $heading = htmlspecialchars($ftitle);
                                    $subtext = "";
                                    $sort_order = 4; // Default for others

                                    // Special formatting for "Hotel" related entries
                                    if (stripos($ftitle, 'Hotel') !== false) {
                                        // Use "Total Hotels" if it's about tie-ups, otherwise "Hotel Access"
                                        if (stripos($ftitle, 'Tie-up') !== false || stripos($ftitle, 'tied Up') !== false) {
                                            $icon = "fa-hotel";
                                            $heading = "Total Hotels";
                                            $sort_order = 1;
                                        } else {
                                            $icon = "fa-key"; // Different icon for Hotel Access
                                            $heading = "Hotel Access";
                                            $sort_order = 2;
                                        }
                                        $subtext = htmlspecialchars($ftitle);
                                    } 
                                    // Special formatting for "Monthly Wallet Income"
                                    elseif (stripos($ftitle, 'Monthly Wallet Income') !== false) {
                                        $icon = "fa-wallet";
                                        $heading = "Wallet Income";
                                        $subtext = htmlspecialchars($ftitle);
                                        $sort_order = 3;
                                    }
                                    // Generic formatting if admin uses a colon "Heading: Subtitle"
                                    elseif (strpos($ftitle, ':') !== false) {
                                        $parts = explode(':', $ftitle, 2);
                                        $heading = htmlspecialchars(trim($parts[0]));
                                        $subtext = htmlspecialchars(trim($parts[1]));
                                    }

                                    $dynamic_features[] = [
                                        'icon' => $icon,
                                        'heading' => $heading,
                                        'subtext' => $subtext,
                                        'sort_order' => $sort_order
                                    ];
                                endwhile; 
                                
                                // Inject static note at the end of dynamic features list
                                $dynamic_features[] = [
                                    'icon' => 'fa-info-circle',
                                    'heading' => 'Note',
                                    'subtext' => '<span style="font-size: 12px;">Investment amount refundable after 3 years</span>',
                                    'sort_order' => 5
                                ];
                                
                                // Sort features consistently
                                usort($dynamic_features, function($a, $b) {
                                    return $a['sort_order'] <=> $b['sort_order'];
                                });
                                
                                // Output the sorted features
                                foreach ($dynamic_features as $df):
                            ?>
                            <li class="mb-3 d-flex align-items-start">
                                <div class="icon-sm me-3 mt-1" style="color: <?php echo $color; ?>;"><i class="fas <?php echo $df['icon']; ?>"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo $df['heading']; ?></h6>
                                    <?php if(!empty($df['subtext'])): ?>
                                        <p class="small text-muted mb-0"><?php echo $df['subtext']; ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <?php endforeach; endif; ?>
                            
                            <?php if($plan_db['company_buyback']): ?>
                            <li class="mb-3 d-flex align-items-start">
                                <div class="icon-sm me-3 mt-1" style="color: <?php echo $color; ?>;"><i class="fas fa-undo"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Company Buyback Guarantee</h6>
                                    <p class="small text-muted mb-0">Included with this plan</p>
                                </div>
                            </li>
                            <?php endif; ?>
                        </ul>
                        
                        <a href="payment.php?plan=<?php echo urlencode($name); ?>" class="btn w-100 py-3 fw-bold text-uppercase mt-auto" style="background-color: <?php echo $color; ?>; color: white; border-radius: 12px; letter-spacing: 1px;margin-bottom:-20px;">Invest Now</a>
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
        <div class="section-title text-center" style="margin-bottom: 25px !important;">
            <span style="color: var(--secondary-color); text-transform: uppercase; letter-spacing: 2px; font-weight: 700; font-size: 0.8rem;">Payment Schedules</span>
            <h2 class="fw-bold mb-4">Investment Installment Plans</h2>
            <p class="text-muted mx-auto" style="max-width: 700px;">Choose your convenient payment structure based on your portfolio size. Clear, transparent, and easy installments.</p>
        </div>

        <div class="row gx-3 gy-3 mt-2">
            <!-- Table 1: Plan A Structure -->
            <div class="col-lg-6 px-0 px-sm-3">
                <div class="premium-card p-3 p-md-4 bg-white border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="text-center mb-3">
                        <div class="icon-box mx-auto text-primary mb-2" style="font-size: 2.2rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: rgba(11, 44, 77, 0.06); border-radius: 50%;">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h4 class="mb-1 fw-bold">Plan Structure A</h4>
                        <span class="badge bg-primary px-3 py-2 rounded-pill mt-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">40% - 30% - 30%</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center small mb-0">
                            <thead class="bg-primary text-white border-0">
                                <tr>
                                    <th class="py-2 border-0">Total Investment</th>
                                    <th class="py-2 border-0">1st (40%)</th>
                                    <th class="py-2 border-0">2nd (30%)</th>
                                    <th class="py-2 border-0">3rd (30%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $resA = $conn->query("SELECT plan_price FROM plans WHERE status = 1 AND (plan_name NOT LIKE '%Plan D%' AND plan_name NOT LIKE '%Plan E%' AND plan_name NOT LIKE '%Plan F%') ORDER BY plan_price ASC");
                                if ($resA && $resA->num_rows > 0) {
                                    while($rowA = $resA->fetch_assoc()) {
                                        $price = $rowA['plan_price'];
                                        $first = $price * 0.40;
                                        $second = $price * 0.30;
                                        $third = $price * 0.30;
                                        echo "<tr>
                                                <td class='fw-bold py-2'>₹" . number_format($price) . "</td>
                                                <td>₹" . number_format($first) . "</td>
                                                <td>₹" . number_format($second) . "</td>
                                                <td>₹" . number_format($third) . "</td>
                                              </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-3 bg-light py-2 px-3 rounded-3 mb-0" style="text-align: justify;"><i class="fas fa-info-circle me-2"></i> For these plans, the total amount is split into 3 parts. Example: For a 21,000 plan, pay 8,400 first, then 6,300, and 6,300 finally.</p>
                </div>
            </div>

            <!-- Table 2: Plan B Structure -->
            <div class="col-lg-6 px-0 px-sm-3">
                <div class="premium-card p-3 p-md-4 bg-white border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="text-center mb-3">
                        <div class="icon-box mx-auto text-secondary mb-2" style="font-size: 2.2rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: rgba(201, 162, 39, 0.08); border-radius: 50%; color: var(--secondary-color) !important;">
                            <i class="fas fa-gem"></i>
                        </div>
                        <h4 class="mb-1 fw-bold">Plan Structure B (Premium)</h4>
                        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill mt-1" style="background-color: var(--secondary-color); font-size: 0.8rem; letter-spacing: 0.5px;">20% - 20% - 60%</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center small mb-0">
                            <thead class="bg-secondary text-white border-0" style="background-color: var(--secondary-color) !important;">
                                <tr>
                                    <th class="py-2 border-0">Total Investment</th>
                                    <th class="py-2 border-0">1st (20%)</th>
                                    <th class="py-2 border-0">2nd (20%)</th>
                                    <th class="py-2 border-0">3rd (60%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $resB = $conn->query("SELECT plan_price FROM plans WHERE status = 1 AND (plan_name LIKE '%Plan D%' OR plan_name LIKE '%Plan E%' OR plan_name LIKE '%Plan F%') ORDER BY plan_price ASC");
                                if ($resB && $resB->num_rows > 0) {
                                    while($rowB = $resB->fetch_assoc()) {
                                        $price = $rowB['plan_price'];
                                        $first = $price * 0.20;
                                        $second = $price * 0.20;
                                        $third = $price * 0.60;
                                        echo "<tr>
                                                <td class='fw-bold py-2'>₹" . number_format($price) . "</td>
                                                <td>₹" . number_format($first) . "</td>
                                                <td>₹" . number_format($second) . "</td>
                                                <td>₹" . number_format($third) . "</td>
                                              </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-3 bg-light py-2 px-3 rounded-3 mb-0" style="text-align: justify;"><i class="fas fa-info-circle me-2"></i> This structure is for high-value investments (6.25L and 12.5L). Pay 20% in the first two installments and 60% as the final part.</p>
                    <p class="text-muted small mt-3 bg-light py-2 px-3 rounded-3 mb-0" style="text-align: justify;"><i class="fas fa-info-circle me-2"></i>Registration charges should be paid by the customer at the time of taking membership.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Secured Asset Policy for Premium Plans -->
<section class="section-padding" style="background-color: rgba(201, 162, 39, 0.05);">
    <div class="container">
        <div class="section-title text-center" style="margin-bottom: 25px !important;">
            <span style="color: var(--secondary-color); text-transform: uppercase; letter-spacing: 2px; font-weight: 700; font-size: 0.8rem;">Premium Plans (Plan D, E, F)</span>
            <h2 class="fw-bold">Secured Asset & Refund Policy</h2>
            <p class="text-muted mx-auto" style="max-width: 800px;">Exclusive real estate security and a flexible, transparent exit strategy for our premium investors.</p>
        </div>

        <div class="row align-items-center mt-4">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="pe-lg-4">
                    <h4 class="fw-bold mb-3" style="color: var(--primary-color);"><i class="fas fa-map-marked-alt text-secondary me-2"></i> Physical Land Security</h4>
                    <p class="text-muted mb-4" style="text-align: justify;">For Plans D, E, and F, we provide a physical plot/land as security for your investment. This ensures your capital is fully backed by tangible real estate from day one.</p>
                    
                    <h4 class="fw-bold mb-3" style="color: var(--primary-color);"><i class="fas fa-hourglass-half text-secondary me-2"></i> 3-Year Investment Lifecycle</h4>
                    <p class="text-muted mb-4" style="text-align: justify;">The investment duration for these premium plans is strictly limited to <strong>3 years (36 months)</strong>.</p>
                    
                    <h4 class="fw-bold mb-3" style="color: var(--primary-color);"><i class="fas fa-hand-holding-usd text-secondary me-2"></i> 100% Refund & Exit Process</h4>
                    <div class="p-4 rounded-4" style="background: white; border-left: 5px solid var(--secondary-color); box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                        <p class="text-muted mb-0" style="text-align: justify;">Once the 36-month period is completed, the company will initiate a full refund of your investment amount within the next <strong>60 days</strong>. Simultaneously, the company will take back the physical plot/land provided as security. Upon completion of this process, the customer is completely free from the agreement.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <img src="assets/imgs/plot.png" alt="Real Estate Security" class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute bottom-0 start-0 m-4 p-3 bg-white rounded-3 shadow" style="max-width: 250px;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt text-success fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0">100% Secure</h6>
                                <small class="text-muted">Land-backed investment</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Detailed Rules & Conditions -->
<section class="section-padding">
    <div class="container">
        <div class="section-title text-center" style="margin-bottom: 25px !important;">
            <span style="color: var(--secondary-color); text-transform: uppercase; letter-spacing: 2px; font-weight: 700; font-size: 0.8rem;">Rules & Guidelines</span>
            <h2 class="fw-bold">Investment Policy (Terms & Conditions)</h2>
        </div>

        <div class="row g-4 justify-content-center mt-2">
            <!-- Policy Block 1: Installment 1 Details -->
            <div class="col-lg-3 col-md-6">
                <div class="premium-card p-4 h-100 border-0 shadow-sm transition-hover" style="border-radius: 20px;">
                    <div class="icon-box-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.2rem;">1</div>
                    <h5 class="fw-bold mb-3 text-center">Installment 1 Details</h5>
                    <p class="small text-muted mb-3" style="text-align: justify;"><strong class="text-dark">1st Installment:</strong> To be paid at the time of taking membership.</p>
                    <p class="small text-muted mb-0" style="text-align: justify;"><i class="fas fa-envelope-open-text text-secondary me-2"></i> Within 1 month, you will receive your Membership Card and official rules/offers via postal mail.</p>
                </div>
            </div>

            <!-- Policy Block 2: Installment 2 Details -->
            <div class="col-lg-3 col-md-6">
                <div class="premium-card p-4 h-100 border-0 shadow-sm transition-hover" style="border-radius: 20px;">
                    <div class="icon-box-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.2rem;">2</div>
                    <h5 class="fw-bold mb-3 text-center">Installment 2 Details</h5>
                    <p class="small text-muted mb-3" style="text-align: justify;"><strong class="text-dark">2nd Installment:</strong> To be paid within 40 to 45 days of the start of your membership.</p>
                    <div class="p-3 rounded-3 small fw-bold mt-2" style="background: rgba(201, 162, 39, 0.1); color: var(--secondary-color); text-align: justify;">
                        <i class="fas fa-calendar-check me-2"></i> Hotel booking facility starts 61 days after the second installment is paid.
                    </div>
                </div>
            </div>

            <!-- Policy Block 3: Installment 3 & Profits -->
            <div class="col-lg-3 col-md-6">
                <div class="premium-card p-4 h-100 border-0 shadow-sm transition-hover" style="border-radius: 20px;">
                    <div class="icon-box-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.2rem;">3</div>
                    <h5 class="fw-bold mb-3 text-center">Installment 3 & Profits</h5>
                    <p class="small text-muted mb-3" style="text-align: justify;"><strong class="text-dark">3rd Installment:</strong> The final payment must be made within 70 to 75 days of receiving your Membership Card.</p>
                    <p class="small text-muted mb-3" style="text-align: justify;"><strong class="text-dark">Rental Income:</strong> 91 days after full payment is received, investors start earning 1.25% to 1.50% annual Rental Income.</p>
                    <div class="p-3 rounded-3 small fw-bold mt-2" style="background: rgba(11, 44, 77, 0.1); color: var(--primary-color); text-align: justify;">
                        <i class="fas fa-hand-holding-usd me-2"></i> Earnings are credited monthly to your secure wallet.
                    </div>
                </div>
            </div>

            <!-- Policy Block 4: Payment Default Policy -->
            <div class="col-lg-3 col-md-6">
                <div class="premium-card p-4 h-100 border-0 shadow-sm transition-hover" style="border-radius: 20px; border-top: 5px solid #dc3545 !important;">
                    <div class="icon-box-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.2rem;"><i class="fas fa-exclamation-triangle" style="font-size: 1.05rem;"></i></div>
                    <h5 class="fw-bold mb-3 text-danger text-center">Payment Default Policy</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-start small text-muted" style="text-align: justify;">
                            <i class="fas fa-times-circle text-danger me-2 mt-1"></i>
                            <span><strong>1st Installment Only:</strong> No profits or stay benefits. Funds returned after 36 months after TDS deduction.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start small text-muted" style="text-align: justify;">
                            <i class="fas fa-exclamation-circle text-warning me-2 mt-1"></i>
                            <span><strong>2 Installments Only:</strong> 3 years of hotel stay facility provided, but no rental income profit.</span>
                        </li>
                        <li class="mb-0 d-flex align-items-start small text-muted" style="text-align: justify;">
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

<?php include('includes/footer.php'); ?>
