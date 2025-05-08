<% if $ShowTitle %>
    <div class="element__title--holder">
        <% if $showSubTitle && not $showSubTitleBelowTitle %>
            <span class="element__subtitle subtitle d-block">
                $SubTitle
            </span>
        <% end_if %>
        <h2 class="element__title">
            $FrontendTitle
        </h2>
        <% if $showSubTitle && $showSubTitleBelowTitle %>
            <span class="element__subtitle subtitle d-block">
                $SubTitle
            </span>
        <% end_if %>
    </div>
<% end_if %>
