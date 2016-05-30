<div class="page-header">
    <h1>Файлы</h1>
</div>

<ul>
    {% for user in users %}
    <li>
        {{ user.name }} {{ user.created_at.diffForHumans() }}
        <ul>
            {% for file in user.files %}
                <li><img src="{{ file.thumb(200,200) }}" width="200"></li>
            {% endfor %}
        </ul>
    </li>
    {% endfor %}

    {% for ftag in tags %}
        <li>
            {{ ftag.name }} {{ ftag.created_at.diffForHumans() }} - {{ ftag.file.name }}
        </li>
    {% endfor %}
</ul>

<form method="post" enctype="multipart/form-data">

    <input name="userfile" type="file" />
    <button type="submit">Отправить</button>
</form>