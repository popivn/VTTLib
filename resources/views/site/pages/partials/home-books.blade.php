<div class="relative group/books-swiper overflow-hidden w-full">
    <div class="swiper books-swiper-container !pb-1">
        <div class="swiper-wrapper flex flex-nowrap">
            @include('site.pages.partials.home-books-slides', ['newBooks' => $newBooks])
        </div>
        
        <!-- Navigation Buttons -->
        <div class="swiper-button-next books-next !w-8 !h-8 !bg-white !rounded-full !shadow-lg !border !border-slate-100 !text-vttu-red after:!text-[10px] !right-2 opacity-0 group-hover/books-swiper:opacity-100 transition-opacity z-30"></div>
        <div class="swiper-button-prev books-prev !w-8 !h-8 !bg-white !rounded-full !shadow-lg !border !border-slate-100 !text-vttu-red after:!text-[10px] !left-2 opacity-0 group-hover/books-swiper:opacity-100 transition-opacity z-30"></div>
        
    </div>
</div>
