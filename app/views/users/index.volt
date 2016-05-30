<h1>Логи</h1>

<ul>
{% for log in logs %}
    <li>
        {{ log.object }} > {{ log.object_id }} > {{ log.action }}
        {% if log.old_value|length > 0 %}
        <ul>
            {% for key, value in log.old_value %}
            <li>{{ key }}: {{ value }} -> {{ log.new_value[key] }}</li>
            {% endfor %}
        </ul>
        {% endif %}
    </li>
{% endfor %}
</ul>
