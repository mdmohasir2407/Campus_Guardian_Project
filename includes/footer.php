<?php
/**
 * CampusGuardian - Global Footer Template
 */
?>
        </div> <!-- End of Content -->
    </div> <!-- End of Wrapper -->

    <!-- jQuery 3.7 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js 4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/chart-custom.js"></script>

    <!-- 3D Background Animation (Vanta.js + Three.js) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof VANTA !== 'undefined') {
                VANTA.NET({
                    el: "#vanta-bg",
                    mouseControls: true,
                    touchControls: true,
                    gyroControls: false,
                    minHeight: 200.00,
                    minWidth: 200.00,
                    scale: 1.00,
                    scaleMobile: 1.00,
                    color: 0x3b82f6,
                    backgroundColor: 0xf8fafc,
                    points: 12.00,
                    maxDistance: 22.00,
                    spacing: 18.00,
                    showDots: true
                });
            }
        });
    </script>
</body>
</html>
