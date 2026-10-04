    </main>
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>سكن كير</h3>
                    <p>متجرك المفضل للعناية بالبشرة</p>
                </div>
                <div class="footer-section">
                    <h4>روابط سريعة</h4>
                    <ul>
                        <li><a href="<?php echo $basePath; ?>index.php">الرئيسية</a></li>
                        <li><a href="<?php echo $basePath; ?>products/index.php">المنتجات</a></li>
                        <li><a href="<?php echo $basePath; ?>about.php">عنّا</a></li>
                        <li><a href="<?php echo $basePath; ?>contact.php">تواصل</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>معلومات</h4>
                    <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?></p>
                </div>
            </div>
        </div>
    </footer>
    <script>
        // تعريف basePath للاستخدام في JavaScript
        window.basePath = '<?php echo $basePath; ?>';
    </script>
    <script src="<?php echo $basePath; ?>assets/js/app.js"></script>
</body>
</html>

