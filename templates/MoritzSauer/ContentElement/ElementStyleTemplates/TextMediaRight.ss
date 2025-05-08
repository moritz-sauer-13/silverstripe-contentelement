<div class="contentelement__image contentelement">
    <div class="container media__right">
        <div class="row align-items-center">
            <% if $ShowTitle || $Content %>
                <div class="<% if $mediaTypeToDeliver %>col-lg-6 col-xl-5 order-last order-lg-first<% else %>col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2<% end_if %> <% if $showContentCentered %> text-center <% end_if %>">
                    <% include ElementTitle %>
                    <div class="mt-3 mt-md-4 mt-lg-5">
                        $Content
                        <% include ElementButton %>
                    </div>
                </div>
            <% end_if %>
            <% if $mediaTypeToDeliver %>
                <div class="<% if $ShowTitle || $Content %> col-lg-6 offset-xl-1 order-first order-lg-last <% else %> col-12 <% end_if %>mt-3 mt-md-4 mt-lg-5">
                    <% include TwoColumnMedia %>
                </div>
            <% end_if %>
        </div>
    </div>
</div>
