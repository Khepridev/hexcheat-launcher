            </div> <!-- İçerik alanının kapanışı -->
        </div> <!-- Flex container kapanışı -->
    </div> <!-- Ana container kapanışı -->

    <script>
    // Menü öğelerini açıp kapatma
    document.querySelectorAll('.menu-item[data-menu]').forEach(item => {
        const button = item.querySelector('button');
        const submenu = item.querySelector('.submenu');
        
        button.addEventListener('click', () => {
            item.classList.toggle('active');
            if (submenu.style.display === 'block' || submenu.style.display === '') {
                submenu.style.display = 'none';
            } else {
                submenu.style.display = 'block';
            }
        });
    });
    
    // SweetAlert için özel tema tanımlaması
    const SwalCustom = Swal.mixin({
        customClass: {
            popup: 'bg-[#111111] border border-white/10',
            title: 'text-white',
            htmlContainer: 'text-white/70',
            confirmButton: 'bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 px-4 py-2 rounded-lg transition-all'
        },
        buttonsStyling: false
    });
    </script>
</body>
</html> 