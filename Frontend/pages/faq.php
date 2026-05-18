<?php 
$is_subpage = true;
include('../includes/header.php'); 
?>
<?php include('../includes/navbar.php'); ?>

<!-- Internal Hero -->
<section class="section-padding bg-primary text-white pt-5 mt-5">
    <div class="container pt-5 text-center">
        <h1 class="display-4 fw-bold">FAQs</h1>
        <p class="lead">Everything you need to know about Stay Vibes.</p>
    </div>
</section>

<!-- FAQ Accordion -->
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="mainFaqAccordion">
                    <!-- Category: Investment -->
                    <h4 class="mb-4 mt-5 fw-bold"><i class="fas fa-coins text-secondary me-2"></i> Investment Questions</h4>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                What is the minimum investment amount?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#mainFaqAccordion">
                            <div class="accordion-body text-muted pb-4">
                                The minimum investment starts from $5,000 (Plan A). This allows you to participate in our resort rental pool and earn monthly income.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How is the monthly rental income calculated?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#mainFaqAccordion">
                            <div class="accordion-body text-muted pb-4">
                                Rental income is calculated as a fixed percentage of your investment amount, ranging from 4% to 12% annually, paid monthly. This is based on the operational revenue of our resort portfolio.
                            </div>
                        </div>
                    </div>

                    <!-- Category: Benefits -->
                    <h4 class="mb-4 mt-5 fw-bold"><i class="fas fa-gift text-secondary me-2"></i> Stay & Membership</h4>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How do I book my free nights?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#mainFaqAccordion">
                            <div class="accordion-body text-muted pb-4">
                                Once your investment is active, you can book your free nights through our investor portal or by contacting your dedicated relationship manager. Bookings are subject to availability and should be made at least 15 days in advance.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Are the memberships transferable?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#mainFaqAccordion">
                            <div class="accordion-body text-muted pb-4">
                                Yes, memberships and investment certificates can be transferred to family members or third parties after an initial lock-in period of 12 months, subject to administrative approval.
                            </div>
                        </div>
                    </div>

                    <!-- Category: Security -->
                    <h4 class="mb-4 mt-5 fw-bold"><i class="fas fa-user-shield text-secondary me-2"></i> Safety & Legal</h4>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Is my capital insured?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#mainFaqAccordion">
                            <div class="accordion-body text-muted pb-4">
                                We maintain a comprehensive insurance policy that covers the physical assets of the resorts. While all investments carry some risk, our diversified portfolio and legal structure are designed to maximize capital protection.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Still have questions? -->
<section class="section-padding bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Still Have Questions?</h2>
        <p class="text-muted mb-5">Our support team is available 24/7 to assist you with any queries.</p>
        <a href="contact.php" class="btn btn-gold btn-lg">Contact Support</a>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
