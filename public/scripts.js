/*
  Функция вставки данных в html шаблон, используется для вставки отзывов с бекенда
  rendering_template - экземпляр DocumentFragment, найденный по id
  data - обычный объект ключ-значение
*/

function render(rendering_template, data) {
  const regexp = /--([a-zA-Z]+)--/g // Ищем все значения формата: --name-- или --id--
  const node = rendering_template.content.cloneNode(true) // Копируем контент шаблона в отдельную html ноду
  const childNode = node.childNodes[1] // Обращаемся к второму дочернему элементу (их 3, первый и последние - пробелы)
  const childNodeContent = childNode.innerHTML // Выстаскиваем из html ноды содержимое в виде строки
  const replacedContent = childNodeContent.replace(regexp, (_match, field) => { 
    // Вызываем метод replace для замены каждого вхождение регулярной строки вызывается анонимная функция с параметром march, который нам не нужен
    // В field будет лежать строка name, если в шаблоне была конструкция --name-- 
    return data[field]
  })
  childNode.innerHTML = replacedContent // Вставляем новый контент в ноду
  return node // И возвращаем её
}

let feeds // Далее тут будет html нода, в которую будем класть новые отзывы
let template // Это переменная для шаблона отзывов
let count = 0 // Это счетчик количества вызовов метода loadFeedback
let secret_code // Сюда мы положим секретный код, который вытащим из параметров запроса

window.onload = () => { // Вызывается после того, как документ, стили и скрипты загрузятся

  let params = new URLSearchParams(location.search); // Для более простого вытаскивания секретного кода используем URLSearchParams
  secret_code = params.get('secret_code')

  if(secret_code){ // Если секретный код есть, надо отобразить форму для отправки отзыва
    const main = document.getElementById('main') // Ищем главную ноду, в которой лежат артикли
    const about = document.getElementById('about') // Ищем ноду about (перед ней форму будем вставлять)

    const feedbackTemplate = document.getElementById('feedback-form-template') // Ищем шаблон формы ждя отзыва
    const node = feedbackTemplate.content.cloneNode(true) // Клонируем содержимое шаблона в новую ноду
    main.insertBefore(node, about) // И вставляем её в ноду main перед about

    const feedbackForm = document.getElementById('feedback-form') // Во вставленном артикле ищем сам тег формы

    feedbackForm.addEventListener('submit', async function(e){ // Добавляем слушатель, который отреагирует на отправку формы
      e.preventDefault(); // Обрываем выполнение отправки формы, что бы самостоятельно обработать данные и отправить её
      // html формы работают с заголовками multipart/form-data, мы же используем application/json, нам необходимо преобразовать
      // параметры формы в json строку
      let formData = new FormData(this); // Мы вытаскиваем объект FormData из формы
      formData = {secret_code, ...Object.fromEntries(formData)} // Мы формируем новый объект, используя деструктуризацию, добавляем туда секретный код
      try{ // Методы работы с сетью (в нашем случае, промисы) могут выбрасывать исключения по разным причинам, их надо обработать
        const res = await fetch('api.php?action=create_feedback', { // Используя fetch API отправляем на наш http сервер запрос
          method: 'POST', // Методом POST
          headers: {
            'Content-Type' : 'application/json' // Обязательно указывая заголовки
          },
          body: JSON.stringify(formData) // Наш объект с данными конвертируем в json строку
        })
    
        const data = await res.json() // Парсим json ответ

        if(data.ok){ // Если сервер нам отправил {ok: 'ok'} - то все норм
          alert('Вы оставили отзыв.') // Кидаем алерт, что все отлично
          window.location = window.location.href.split("?")[0]; // Перезагружаем страницу, но убираем из url все доп. параметры (secret_code)
        } else {
          alert('Ошибка валидации формы') // Если сервер вернул что-то другое, то, скорее всего - это ошибка валидации формы
        }
      } catch(e) { // Если поймали исключение - произошла фатальная ошибка
        alert('Что-то пошло не так, приносим свои извинения :(')
      }
    })
    
  }

  feeds = document.getElementById('feedbacks-container') // Нужно достать ноду, в которую будем складывать отзывы
  template = document.getElementById('feedback-template') // А так же шаблон

  loadFeedback() // Вызываем метод loadFeedback, он загрузит нам первые 5 отзывов

  const form = document.getElementById('form') // Ищем форму
  form.addEventListener('submit', async function(e){ // И вешаем слушатель событий,
    e.preventDefault(); // Все то же самое, что и в предыдущем функционале

    let formData = new FormData(this);
    formData = Object.fromEntries(formData)
    try{
      const res = await fetch('api.php?action=create_consultation', {
        method: 'POST',
        headers: {
          'Content-Type' : 'application/json'
        },
        body: JSON.stringify(formData)
      })

      const data = await res.json()

      if(data.ok){
        alert('Вы записались на консультацию, вам перезвонят в ближайшее время.')
        location.reload()
      } else {
        alert('Ошибка валидации формы')
      }
    } catch(e) {
      alert('Что-то пошло не так, приносим свои извинения :(')
    }
  })
}

async function loadFeedback(){
  const response = await fetch(`api.php?action=get_feedbacks&page=${count++}`)
  const data = await response.json()
  if(data.length < 5){
    // Если с сервера вернулось менее 5 отзывов - значит - больше их нет, надо спрятать кнопку "Загрузить ещё"
    document.getElementById('load-button').style.visibility = 'hidden';
  }
  // Перебирая все отзывы, которые вернулись с сервера, мы для каждого вызываем render, и добавляем их в список отзывов
  data.forEach((feedback) => {
    feeds.appendChild(render(template, feedback))
  })
}

