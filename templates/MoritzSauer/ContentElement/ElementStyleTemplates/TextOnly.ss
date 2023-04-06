<div class="contentelement__textonly contentelement my-4 my-md-5">
    <div class="container typography">
        <div class="row">
            <% if $ShowTitle || $Content %>
                <div class="col-12  <% if $showContentCentered %> col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 text-center <% else %> col-lg-10 col-xl-8 <% end_if %>">
                    <% include ElementTitle %>
                    $Content
                    <% include ElementButton %>
                </div>
            <% end_if %>
        </div>
    </div>
</div>