<footer class="sticky-footer bg-white">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-center">
            <div class=" mb-md-0">
                {{ session('current_company_data')->name ?? '' }}
            </div>

            <div>
                <span>Copyright &copy; WorkHub {{ date('Y') }}</span>
            </div>
        </div>
    </div>
</footer>
