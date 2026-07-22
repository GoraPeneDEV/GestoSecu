<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @isset($pageConfigs)
    {!! Helper::updatePageConfig($pageConfigs) !!}
    @endisset

    @php
    $configData = Helper::appClasses();
    @endphp
</head>

<body>
    @isset($configData['layout'])
    @include($configData['layout'] === 'blank' ? 'layouts.blankLayout' : 'layouts.contentNavbarLayout')
    @endisset
</body>

</html>
