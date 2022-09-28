define(['jquery', 'jqueryui', 'block_stack/main'], function () {
    return {
        init: function () {
            window.console.log('teacher');
            var children = document.querySelectorAll("#menu-courses");

            for (let i = 0; i < children.length; i++) {
                children[i].addEventListener("click", function(evt){
                    var child_id = evt.target.id.split("-");
                    require(['core/ajax','core/notification'], function(Ajax,Notification) {
                        window.console.log(Ajax);
                        var request = {
                            methodname: 'block_stack_get_chart_db',
                            args : { id_course : child_id[1] }
                        };

                        var promise = Ajax.call([request])[0];
                        
                        promise.done(function(response) {
                            require(['core/chartjs'], function() {
                                const cv = document.createElement('canvas');
                                cv.setAttribute('id', 'myChart');
                                document.querySelector('#render-chart').appendChild(cv);
                                const ctx = cv.getContext('2d');
                                const myChart = new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: [
                                            'Superados',
                                            'No Superados',
                                            ],
                                        datasets: [{
                                            label: '% de estudiantes',
                                            title: 'Total de cuestionarios con más de un intento',
                                            data: [response["passed"], response["not_passed"]],
                                            backgroundColor: [
                                                'rgb(119, 221, 119)',
                                                'rgb(255, 0, 0)'
                                            ],
                                            hoverOffset: 4
                                        }]
                                    }
                                });
                            });
                            document.getElementById('course_not_selected').hidden = true;
                            window.console.log(response);
                        }).fail(function(ex){
                            window.console.log(ex);
                        });
                    });
                });
            }
        }, init_student : function() {
            window.console.log('student');
            var children = document.querySelectorAll("#menu-courses");

            for (let i = 0; i < children.length; i++) {
                children[i].addEventListener("click", function(evt){
                    var child_id = evt.target.id.split("-");
                    require(['core/ajax','core/notification'], function(Ajax,Notification) {
                        window.console.log(Ajax);
                        var request = {
                            methodname: 'block_stack_get_chart_db_student',
                            args : { id_course : child_id[1] }
                        };

                        var promise = Ajax.call([request])[0];
                        
                        promise.done(function(response) {
                            require(['core/chartjs'], function() {
                                const cv = document.createElement('canvas');
                                cv.setAttribute('id', 'myChart');
                                document.querySelector('#render-chart').appendChild(cv);
                                const ctx = cv.getContext('2d');
                                const myChart = new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: [
                                            'Superados',
                                            'No Superados',
                                            ],
                                        datasets: [{
                                            label: '% de estudiantes',
                                            title: 'Total de cuestionarios con más de un intento',
                                            data: [response["passed"], response["not_passed"]],
                                            backgroundColor: [
                                                'rgb(119, 221, 119)',
                                                'rgb(255, 0, 0)'
                                            ],
                                            hoverOffset: 4
                                        }]
                                    }
                                });
                            });
                            document.getElementById('course_not_selected').hidden = true;
                            window.console.log(response);
                        }).fail(function(ex){
                            window.console.log(ex);
                        });
                    });
                });
            }
        },
        search: function() {
            var input = document.querySelector('input');
            input.addEventListener("input", function(evt) {
                window.console.log(evt.target.value);
                require(['core/ajax','core/notification'], function(Ajax,Notification) {
                    window.console.log(Notification);
                    var request = {
                        methodname: 'block_stack_get_table_students',
                        args : { input_value :  evt.target.value}
                    };
                    var promise = Ajax.call([request])[0];
                    promise.done(function(response) {
                        window.console.log(response);
                        if( document.querySelector('#students-list') != null) {
                            $('#students-list').remove();
                        }
                        const ul = document.createElement("ul");
                        ul.setAttribute("id", "students-list");
                        ul.setAttribute("class", "list-group");
                        response.forEach(element => {
                            const item = document.createElement("li");
                            item.setAttribute("class", "list-group-item");
                            //item.setAttribute("id", element["name"]);
                            ul.appendChild(item);
                            item.innerHTML = item.innerHTML+element["name"];
                        });
                        const nav = document.querySelector('[role="main"]').appendChild(ul);
                        var children = document.querySelectorAll("#students-list li");
                        for (let i = 0; i < children.length; i++) {
                            children[i].addEventListener("click", function(evt){
                                $('#search-student').val(children[i].innerText);
                                window.console.log(children[i]);
                                children[i].removeAttribute("class");
                                children[i].setAttribute("class", "list-group-item active");
                            });
                        };
                    }).fail(function(ex){
                        window.console.log(ex);
                    });
                });
            });
        },
        graph: function() {
            var mathjax = document.createElement('script');
            mathjax.src = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js';
            mathjax.id = 	'MathJax-script';
            mathjax.async = true;
            document.getElementsByTagName("head")[0].appendChild(mathjax);
            window.MathJax = {
                tex: {
                    inlineMath: [['$', '$'], ['\\(', '\\)']]
                },
                svg: {
                    fontCache: 'global'
                }
            };

            //const selection = document.querySelector('.custom-select').selectedOptions[0].value;
            const selection = document.querySelector('.custom-select');
            selection.addEventListener('change', function(evt) {
                require(['core/ajax','core/notification'], function(Ajax,Notification) {
                    window.console.log('Entrada'+selection.selectedOptions[0].value);
                    var request = {
                        methodname: 'block_stack_get_questions_shown',
                        args : { selected_item :  selection.selectedOptions[0].value }
                    };
                    var promise = Ajax.call([request])[0];
                    promise.done(function(response) {
                        if(document.querySelector('#container-questions .row')) {
                            document.querySelector('#container-questions .row').remove();
                        }
                        if(document.querySelector('.svg-graph')) {
                            document.querySelector('.svg-graph').remove();
                        }
                        window.console.log(response);
                        if (document.querySelectorAll('.custom-select')[1]) {
                            document.querySelectorAll('.custom-select')[1].remove();
                        }

                        if (document.querySelector('.user')) {
                            document.querySelector('.user').remove();
                        }
                        const user_row = document.createElement('div');
                        const select_intentos = document.createElement('select');
                        select_intentos.setAttribute('class', 'custom-select');
                        select_intentos.style.marginTop = '40px';
                        
                        const option = document.createElement('option');
                        option.value = '-1';
                        option.text = '-Selecciona un intento';
                        option.selected;
                        select_intentos.appendChild(option);

                        document.querySelector('#container-questions').appendChild(user_row);
                        document.querySelector('#container-questions').appendChild(select_intentos);
                        
                        for (var i  = 0; i < response.length; i++) {
                            const boolean = false;
                            for (var j = 0; j < document.getElementsByTagName('select')[1].childElementCount; j++) {
                                if (document.getElementsByTagName('select')[1].children[j].value == response[i]['attempt']) {
                                    boolean = true;
                                }
                            }
                            if(!boolean) {
                                const option = document.createElement('option');
                                option.value = response[i]['attempt'];
                                option.text = 'Intento: ' + response[i]['attempt'];
                                select_intentos.appendChild(option);
                            }
                        }

                        user_row.setAttribute('class', 'user');
                        user_row.style.backgroundColor = 'hwb(180deg 94% 2%)';
                        user_row.style.margin = 'inherit';
                        user_row.style.height = '300px';
                        const canvas = document.createElement('canvas');
                        user_row.appendChild(canvas);


                        var intentos = [];
                        var notas = [];

                        for (var i = 0; i < response.length; i++) {
                            intentos[i] = 'Intento '+response[i]['attempt'];
                            notas[i] = response[i]['mark'];
                        }

                        require(['core/chartjs'], function() {
                            const ctx = document.getElementsByTagName('canvas')[0].getContext('2d');
                            const myChart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: intentos,
                                    datasets: [{
                                        label: 'Evolución de las calificaciones atendiendo a los intentos',
                                        data: notas,
                                        fill: false,
                                        borderColor: 'rgb(75, 192, 192)',
                                        tension: 0.1
                                    }]
                                }
                            });
                        });

                        const row = document.createElement('div');
                        row.setAttribute('class', 'row');
                        row.style.backgroundColor = 'beige';
                        row.style.margin = 'inherit';

                        document.querySelectorAll('.custom-select')[1].addEventListener('change', function(evt){
                            if(document.querySelector('#container-questions .row')) {
                                document.querySelector('#container-questions .row').innerHTML = '';
                            }
                            if(document.querySelector('.svg-graph')) {
                                document.querySelector('.svg-graph').remove();
                            }

                            response.forEach(element => {
                                const attempt = document.querySelectorAll('.custom-select')[1].selectedIndex;
                                if (element['attempt'] == attempt) {
                                    const colg = document.createElement('div');
                                    colg.setAttribute('class', 'col-lg-4 col-md-6 col-sm-6');
                                    row.appendChild(colg);
                                    
                                    const category = document.createElement('div');
                                    category.setAttribute('class', 'category mb-30');
                                    colg.appendChild(category);
                                    category.style.paddingTop = '50px';
                                    category.style.paddingBottom = '50px';
                                    
                                    const question = document.createElement('div');
                                    question.setAttribute('class', 'question');
                                    question.setAttribute('id', element['id']);
                                    question.style.height = '200px';
                                    question.style.border = '1px solid transparent';
                                    question.style.padding = '30px 19px 25px 19px';
                                    question.style.borderRadius = '5px';
                                    question.style.boxShadow = 'rgba(0, 0, 0, 0.35) 0px 5px 15px';
                                    question.style.cursor = 'pointer';
                                    question.style.overflow = 'auto';
                                    category.appendChild(question);
        
        
                                    const name = document.createElement('span');
                                    name.setAttribute('class', 'colors1 mb-4');
                                    name.style.fontWeight = '700';
                                    question.appendChild(name);
                                    name.innerHTML = element['name'];
        
                                    const mark = document.createElement('span');
                                    mark.setAttribute('class', 'colors1 mb-4');
                                    question.appendChild(mark);
                                    mark.innerHTML = '<br> Puntuación: ' + element['mark'] + '/' + element['maxgrade'];
    
                                    const h3 = document.createElement('h3');
                                    h3.innerHTML = 'Preguntas';
                                    h3.style.paddingTop = '15px';
                                    h3.style.paddingLeft = '1px';
                                    h3.style.position = 'absolute';
                                    row.appendChild(h3);
                                }
                            });

                            document.querySelector('#container-questions').appendChild(row);
                            window.console.log(row);
                            
                            var list = document.querySelectorAll('.question');

                            list.forEach(function(userItem) {
                                userItem.onmouseover = function() {
                                    userItem.style.borderColor = '#0d6efd';
                                }
                                userItem.onmouseout = function() {
                                    userItem.style.borderColor = 'transparent';
                                }

                                userItem.addEventListener('click', function() {
                                    if(document.querySelector('.svg-graph')) {
                                        document.querySelector('.svg-graph').remove();
                                    }
                                    response.forEach(function(q) {
                                        if(q['id'] == userItem.id) {
                                            window.console.log(q);
                                            window.console.log(userItem);
                                            const graph = document.createElement('div');
                                            graph.setAttribute('class', 'svg-graph');
                                            graph.style.backgroundColor = 'bisque';
                                            graph.style.marginTop = '50px';
                                            graph.style.display = 'grid';
                                            const svg = JSON.parse(q['graph']);
                                            window.console.log(svg);
                                            if(Array.isArray(svg)) {
                                                svg.forEach(function(element) {
                                                    window.console.log(element);
                                                    const divsvg = document.createElement('div');
                                                    divsvg.innerHTML += element;
                                                    graph.appendChild(divsvg);
                                                });
                                            }else{
                                                graph.appendChild(svg);
                                            }
                                            document.querySelector('#container-questions').appendChild(graph);     
                                            const fillgraph = document.querySelectorAll('.stack_abstract_graph');
                                            const evaluation = JSON.parse(q['nodes']);
                                            const errors = JSON.parse(q['error']);

                                            var it = 0;
                                            fillgraph.forEach(function(tag){
                                                window.console.log(tag);
                                                const atag = tag.getElementsByTagName('a');
                                                for (var i = 0; i < atag.length; i++) {
                                                    if (evaluation[it][i] != null) {
                                                        const splitted = evaluation[it][i].split('-');
                                                        if(splitted[2] == 'T') {
                                                            atag[i].getElementsByTagName('circle')[0].style.fill = 'green';
                                                        }else if(splitted[2] == 'F') {
                                                            atag[i].getElementsByTagName('circle')[0].style.fill = 'red';
                                                        }else {
                                                            evaluation[it].splice(evaluation[it][i], 1);
                                                            i--;
                                                        }
                                                    }else{
                                                        atag[i].getElementsByTagName('circle')[0].style.fill = 'grey';
                                                    }
                                                }
                                                it++;
                                            });

                                            it = 0;
                                            document.querySelectorAll('.svg-graph > div').forEach(function(dv) {
                                                dv.style.alignSelf = 'center';
                                                dv.style.justifySelf = 'center';
                                                dv.style.textAlign = 'center';
                                                dv.innerHTML += errors[it];
                                                window.MathJax.typesetPromise([dv]).then(() => {
                                                    // the new content is has been typeset
                                                    window.MathJax.startup.document.state(0);
                                                    window.MathJax.texReset();
                                                    window.MathJax.typeset();
                                                    });
                                                it++;
                                            });
                                        }
                                    });
                                });
                            });
                        });
                    }).fail(function(ex){
                        window.console.log(ex);
                    });
                });
            });
        },
        excel: function() {
            const buttexchange = document.querySelector('#exchange');
            buttexchange.addEventListener('click', function() {
                require(['core/ajax', 'core/notification'], function(Ajax, Notification){
                    const request = {
                        methodname: 'block_stack_excel',
                        args : { default_parameter :  1 }
                    };
                    const promise = Ajax.call([request])[0];
                    promise.done(function(response) {
                        try {
                            var workbook = window.XLSX.utils.book_new(),
                                worksheet = window.XLSX.utils.aoa_to_sheet(data);
                            workbook.SheetNames.push("First");
                            workbook.Sheets["First"] = worksheet;
                            // (C3) TO BINARY STRING
                            var xlsbin = window.XLSX.write(workbook, {
                            bookType: "xlsx",
                            type: "binary"
                            });
                            
                            // (C4) TO BLOB OBJECT
                            var buffer = new ArrayBuffer(xlsbin.length),
                                array = new Uint8Array(buffer);
                            for (var i=0; i<xlsbin.length; i++) {
                            array[i] = xlsbin.charCodeAt(i) & 0XFF;
                            }
                            var xlsblob = new Blob([buffer], {type:"application/octet-stream"});
                            // (C5) "FORCE DOWNLOAD"
                            var url = window.URL.createObjectURL(xlsblob),
                                anchor = document.createElement("a");
                            anchor.href = url;
                            anchor.download = "demo.xlsx";
                            anchor.click();
                            window.URL.revokeObjectURL(url);
                        }catch(err) {
                            window.console.log(err);
                        }
                    }).fail(function(error){
                        window.console.log(error);
                    });
                });
            });
            window.console.log(buttexchange);
        }
    };
});