<div class="contentelement__twocolumns contentelement">
    <div class="container">
        <div class="row">
            <% if $ShowTitle %>
                <div class="col-12 <% if $showContentCentered %> text-center <% end_if %>">
                    <% include ElementTitle %>
                </div>
            <% end_if %>
            <div class="col-lg-6 mt-3 mt-md-4 <% if $showContentCentered %> text-center <% end_if %>">
                $Content
                <% include ElementButton %>
            </div>
            <div class="col-lg-6 mt-3 mt-md-4 <% if $showContentCentered %> text-center <% end_if %>">
                $SecondContent
            </div>
        </div>
    </div>
</div>
