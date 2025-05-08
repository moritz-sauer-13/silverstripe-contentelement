<% if $LinkedPage %>
    <% with $LinkedPage %>
        <a href="$URL" <% if $OpenInNew %> target="_blank" rel="noopener noreferrer" <% end_if %> class="<% if $Up.class %> $class <% else %> btn btn-primary <% end_if %>">
            $Title
        </a>
    <% end_with %>
<% end_if %>
