<% if $mediaTypeToDeliver == 'Gallery' %>
    <% require themedCSS('client/scss/_gallery') %>
<% end_if %>

<div class="contentelement__mediafullscreen contentelement my-4 my-md-5">
    <div class="container typography">
        <div class="row">
            <% if $ShowTitle || $Content %>
                <div class="col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 <% if $showContentCentered %> text-center <% end_if %>">
                    <% include ElementTitle %>
                    $Content
                    <% include ElementButton %>
                </div>
            <% end_if %>
        </div>
        <% if $mediaTypeToDeliver %>
            <div class="row mt-3 mt-md-4 mt-lg-5">
                <div class="media position-relative">
                    <% if $mediaTypeToDeliver == 'Image' %>
                        <img src="$Image.FocusFill(2560,1080).Link" class="img-fluid">
                    <% else_if $mediaTypeToDeliver == 'Video' %>
                        <video id="video--{$ID}" class="w-100" poster="$PreviewImage.FocusFill(2560,1440).Link" $videoAttributes playsinline>
                            <source src="$VideoMP4.Link" type="video/mp4">
                            <source src="$VideoOGV.Link" type="video/ogv">
                            <source src="$VideoWEBM.Link" type="video/webm">
                        </video>
                    <% else_if $mediaTypeToDeliver == 'Gallery' %>
                        <div class="swiper-container" id="gallery--{$ID}">
                            <div class="swiper-wrapper">
                                <% loop $sortedGalleryImages %>
                                    <div class="swiper-slide">
                                        <picture>
                                            <source srcset="$FocusFill(2560,1080).Link" media="(min-width:768px)">
                                            <source srcset="$FocusFill(800,1080).Link" media="(min-width:1px)">
                                            <img src="$FocusFill(2560,1080).Link" class="img-fluid">
                                        </picture>
                                    </div>
                                <% end_loop %>
                            </div>
                            <% if $showGalleryArrows %>
                                <div class="swiper__controls">
                                    <span class="prev">
                                        <i class="fal fa-arrow-left"></i>
                                    </span>
                                    <span class="next">
                                        <i class="fal fa-arrow-right"></i>
                                    </span>
                                </div>
                            <% end_if %>
                        </div>
                        <% if $sortedGalleryImages.Count > 1 %>
                            <script>
                                window.addEventListener('DOMContentLoaded', () => {
                                    let swiper{$ID} = new Swiper('#gallery--{$ID}', {
                                        slidesPerView: 1,
                                        <% if $showGalleryArrows %>
                                            navigation: {
                                                nextEl: '#gallery--{$ID} .swiper__controls .next',
                                                prevEl: '#gallery--{$ID} .swiper__controls .prev',
                                            },
                                        <% end_if %>
                                    })
                                })
                            </script>
                        <% end_if %>
                    <% end_if %>
                    <% include ElementMediaBadge %>
                    <% include AdditionalMediaElements %>
                </div>
            </div>
        <% end_if %>
    </div>
</div>