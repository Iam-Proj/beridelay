{% block scripts %}
    {{ captcha_script }}
{% endblock %} 

{% if captcha_success is defined %}
    {{ captcha_success }}
{% endif %}

<hr />

<form action="/test/captcha" method="post">
    
    <div><input type="text" name="xdhsgtr1" /></div>
    <div><input type="text" name="xdhsgtr2" /></div>
    <div><input type="text" name="xdhsgtr3" /></div>
    
    {{ captcha_element }}
    
    <button type="submit">Отправить</button>
    
</form>
