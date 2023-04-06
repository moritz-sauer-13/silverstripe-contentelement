<% if $ImageFormat == 'TreeToTwo' %>
    <picture>
        <source srcset="$Image.FocusFill(1536,1024).Link" media="(min-width: 768px)">
        <source srcset="$Image.FocusFill(768,512).Link" media="(min-width: 1px)">
        <img src="$Image.FocusFill(1536,1024).Link" alt="$Image.Title" class="img-fluid">
    </picture>
<% else_if $ImageFormat == 'OneToOne' %>
    <picture>
        <source srcset="$Image.FocusFill(1000,1000).Link" media="(min-width: 768px)">
        <source srcset="$Image.FocusFill(768,768).Link" media="(min-width: 1px)">
        <img src="$Image.FocusFill(1000,1000).Link" alt="$Image.Title" class="img-fluid">
    </picture>
<% else_if $ImageFormat == 'ThreeToFour' %>
    <picture>
        <source srcset="$Image.FocusFill(1272,1698).Link" media="(min-width: 768px)">
        <source srcset="$Image.FocusFill(636,848).Link" media="(min-width: 1px)">
        <img src="$Image.FocusFill(1272,1698).Link" alt="$Image.Title" class="img-fluid">
    </picture>
<% else_if $ImageFormat == 'SixteenToNine' %>
    <picture>
        <source srcset="$Image.FocusFill(2560,1440).Link" media="(min-width: 768px)">
        <source srcset="$Image.FocusFill(1280,720).Link" media="(min-width: 1px)">
        <img src="$Image.FocusFill(2560,1440).Link" alt="$Image.Title" class="img-fluid">
    </picture>
<% end_if %>