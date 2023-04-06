<% if $mediaTypeToDeliver == 'Gallery' %>
    <% require themedCSS('client/scss/_gallery') %>
<% end_if %>

<div class="media position-relative">
    <% if $mediaTypeToDeliver == 'Image' %>
        <% include MediaImageFormats %>
        <% include ImageCaption Caption=$Image.Title %>
    <% else_if $mediaTypeToDeliver == 'Video' %>
        <video id="video--{$ID}" class="w-100" poster="$PreviewImage.FocusFill(756,411).Link" $videoAttributes playsinline>
            <source src="$VideoMP4.Link" type="video/mp4">
            <source src="$VideoOGV.Link" type="video/ogv">
            <source src="$VideoWEBM.Link" type="video/webm">
        </video>
        <% include ImageCaption Caption=$PreviewImage.Title %>
    <% else_if $mediaTypeToDeliver == 'Gallery' %>
        <div class="swiper-container overflow-hidden" id="gallery--{$ID}">
            <div class="swiper-wrapper">
                <% loop $sortedGalleryImages %>
                    <div class="swiper-slide">
                        <% include MediaImageFormats Image=$Me, ImageFormat=$Up.ImageFormat %>
                        <% include ImageCaption Caption=$Title, showImageCaption=$Up.showImageCaption %>
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