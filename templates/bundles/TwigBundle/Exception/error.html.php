% extends 'base.html.twig' %}

{% block body %}

    <h1>Error</h1>
    <h3>The server returned a "{{status_code}}: {{status_text}}"</h3>    
    
{% endblock %}