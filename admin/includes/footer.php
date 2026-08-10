        </main>
        
        <!-- System Footer -->
        <footer class="bg-slate-950 p-6 border-t border-slate-800 text-center text-xs text-slate-500">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <p>&copy; <?= date('Y') ?> Lombok Travel Agency. Hak Cipta Dilindungi.</p>
                <p>Dikembangkan dengan PHP Native & Tailwind CSS</p>
            </div>
        </footer>
    </div>

    <!-- Script for mobile menu interaction -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('sidebar');
            
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                    sidebar.classList.toggle('flex');
                    sidebar.classList.toggle('fixed');
                    sidebar.classList.toggle('inset-0');
                    sidebar.classList.toggle('z-40');
                    sidebar.classList.toggle('top-[57px]'); // Height of mobile top navbar
                });
            }
        });
    </script>
</body>
</html>
