<?php
/**
 * Plugin Name: Adivina la Mezcla
 * Description: Un plugin para adivinar la mezcla de un vino secreto.
 * Version: 1.1
 * Author: Joan Morales
 * AuthoURI: https://joanmorales.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function adivina_la_mezcla_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'url_base' => 'http://tudominio.com/',
        'mezcla_real' => '{}',
        'colores' => '{}',
        'boton' => 'Descúbrelo',
        'imagen-btn' => '',
    ), $atts, 'adivina_la_mezcla' );

    $mezcla_real = json_decode( $atts['mezcla_real'], true );
    $colores = json_decode( $atts['colores'], true ); // Decodificar los colores

    if ( ! $mezcla_real ) {
        return 'Error: La mezcla real no está definida correctamente.';
    }

    ob_start(); // Iniciar el buffer de salida
    ?>
        <!-- Estilos personalizados para los sliders -->
        <style>
            /* Estilos CSS existentes para los sliders */
            .sliders{
                margin-top: 50px;                
            }

            input[type='range'] {
                width: calc(100% - 100px);
                -webkit-appearance: none; /* Safari */
                appearance: none;
                width: 100%;
                height: 25px; /* Altura del track */
                background: transparent; /* Fondo transparente para usar el background degradado */
                outline: none; /* Eliminar el contorno en foco */
                /*opacity: 0.7; /* Transparencia */
                -webkit-transition: opacity .2s; /* Transición de opacidad */
                transition: opacity .2s;
                border-radius:99px;
                border:none;
            }

            input[type='range']::-webkit-slider-thumb {
                -webkit-appearance: none; /* Safari */
                appearance: none;
                width: 5px; /* Ancho del thumb */
                height: 25px; /* Altura del thumb */
                border-radius:9px;
                
                background: #FF4151; /* Color de fondo del thumb */
                cursor: pointer; /* Cursor a puntero */
                
            }

            input[type='range']::-moz-range-thumb {
                width: 5px; /* Ancho del thumb */
                height: 25px; /* Altura del thumb */
                background: #4CAF50; /* Color de fondo del thumb */
                cursor: pointer; /* Cursor a puntero */
            }
            .slider-label {
                color: #000; /* Color del texto */
                font-size: 12px; /* Tamaño del texto */
                text-align: right; /* Alineación del texto a la derecha */
                border-radius:99px;
                background-color: #f4f4f4;
                padding:0px 5px;
                margin-top:1px;
                margin-bottom:1px;
            }

            /* Oculta visualmente el checkbox, pero sigue siendo accesible */
            :root {
                /* Variables de colores para fácil personalización */
                --boton-bg-color: #f0f0f0; /* Color de fondo del botón */
                --boton-bg-color-activo: #4CAF50; /* Color de fondo del botón activo */
                --boton-texto-color: #000; /* Color del texto del botón */
                --boton-texto-color-activo: #fff; /* Color del texto del botón activo */
                --boton-borde-color: #dcdcdc; /* Color del borde del botón */
            }

            /* Estilos para ocultar el checkbox */
            .cepa-checkbox {
                opacity: 0;
                position: absolute;
                z-index: -1;
            }

            /* Estilo para el label que funciona como botón */
            .cepa-label {
                display: inline;
                text-align: center;
                background-color: var(--boton-bg-color-inactivo); /* Color inactivo con opacidad */
                color: var(--boton-texto-color-inactivo);
                border: 2px solid var(--boton-borde-color);
                padding: 10px 10px;
                margin: 5px 0;
                cursor: pointer;
                transition: background-color 0.3s, color 0.3s; 
                width: 100%;
                border-radius: 20px; 
                line-height:55px;
                white-space: nowrap;
                border:none;

            }

            /* Estilo cuando el checkbox está marcado */
            .cepa-checkbox:checked + .cepa-label {
                background-color: var(--boton-bg-color-activo);
                color: var(--boton-texto-color-activo);
            }
            @keyframes popEffect {
                from {
                    transform: scale(0.5);
                    opacity: 0;
                }
                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            .slider-animation {
                animation: popEffect 0.5s ease-out forwards;           
            }

            
    </style>
   

    
    
    <!-- Checkbox para cada cepa -->
    <div id="cepasCheckboxes" style="width: 100%; text-align:center;">
        <?php foreach ( $mezcla_real as $cepa => $porcentaje ): ?>
            <?php
            $cepa_formateada = str_replace('_', ' ', $cepa);
            $color = isset($colores[$cepa]) ? $colores[$cepa] : '#4CAF50'; // Asegura que se usa el color definido o uno por defecto
            ?>
            <input type="checkbox" class="cepa-checkbox" id="<?php echo $cepa; ?>Checkbox" <?php echo $cepa === array_key_first( $mezcla_real ) ? 'checked disabled' : ''; ?> onchange="toggleSliderVisibility('<?php echo $cepa; ?>')">
            <label for="<?php echo $cepa; ?>Checkbox" class="cepa-label" style="--boton-bg-color-inactivo: <?php echo $color; ?>1c; --boton-bg-color-activo: <?php echo $color; ?>;" data-estado-inactivo="true"><?php echo ucfirst($cepa_formateada); ?></label>
        <?php endforeach; ?>
    </div>



    <!-- Sliders para cada cepa -->
    <p style="text-align: center;">Suma total: <span id="sumaTotal" >100%</span></p>
    
    <div id="sliders">
        <?php foreach ( $mezcla_real as $cepa => $porcentaje ): ?>
            <?php
            $cepa_formateada = str_replace('_', ' ', $cepa);
            $color = isset($colores[$cepa]) ? $colores[$cepa] : '#4CAF50'; // Asegura que se usa el color
            ?>
            <div class="<?php echo $cepa; ?>Slider" style="display: <?php echo $cepa === array_key_first( $mezcla_real ) ? 'block' : 'none'; ?>; position: relative; margin-bottom: 20px;">
                <input type="range" id="<?php echo $cepa; ?>" class="slider" data-color="<?php echo $color; ?>" min="0" max="100" value="<?php echo $cepa === array_key_first( $mezcla_real ) ? $porcentaje : '0'; ?>" <?php echo $cepa === array_key_first( $mezcla_real ) ? '' : ''; ?> oninput="updateSliderFill(this)">
                <div id="<?php echo $cepa; ?>Label" class="slider-label" style="position: absolute; right: 0; top: -20px;"><?php echo ucfirst($cepa_formateada); ?>: <span id="<?php echo $cepa; ?>Value"><?php echo $cepa === array_key_first( $mezcla_real ) ? $porcentaje.'%' : '0%'; ?></span></div>
            </div>
        <?php endforeach; ?>
    </div>
    <center>
        <button onclick="calcular()" style="cursor:pointer; color:#000000; background-color: #FFFFFF; border:none; background-image: url('<?php echo esc_url($atts['imagen-btn']); ?>'); background-repeat: no-repeat; background-position: center; background-size: contain; padding: 30px; width: 250px; font-size: 16px; font-weight: bold; text-transform: uppercase;"><?php echo esc_html($atts['boton']); ?></button>
    </center>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mezclaReal = <?php echo json_encode($mezcla_real); ?>;
        const urlBase = "<?php echo esc_js($atts['url_base']); ?>";
        const sliders = document.querySelectorAll('input[type=range]');
        const sumaTotalElemento = document.getElementById('sumaTotal');

        // Función para mostrar/ocultar sliders basada en checkboxes
        window.toggleSliderVisibility = function(cepa) {
            const checkbox = document.getElementById(cepa + 'Checkbox');
            const isChecked = checkbox.checked;
            const label = document.querySelector('label[for="' + cepa + 'Checkbox"]');
            const slider = document.querySelector('.' + cepa + 'Slider');

            slider.style.display = isChecked ? 'block' : 'none';

            if (!isChecked) {
                // Resetear el valor del slider y actualizar la suma total cuando se desmarca
                const sliderInput = document.getElementById(cepa);
                sliderInput.value = 0;
                document.getElementById(cepa + 'Value').innerText = '0%';
                actualizarSumaTotal();
                
                // Restablecer el color de fondo y de texto para el estado inactivo
                label.style.backgroundColor = ''; // Limpia el estilo inline para permitir que se apliquen los estilos CSS
                label.classList.add('estado-inactivo'); // Suponiendo que tienes una clase para el estado inactivo
                label.classList.remove('estado-activo'); // Remueve la clase de estado activo si existe
            } else {
                slider.style.display = 'flex';
                slider.classList.add('slider-animation');
                
                // Aplicar el color de fondo y de texto para el estado activo
                label.style.backgroundColor = ''; // Limpia el estilo inline
                label.classList.remove('estado-inactivo');
                label.classList.add('estado-activo'); // Suponiendo que tienes una clase para el estado activo
            }
        };




        sliders.forEach(slider => {
            slider.addEventListener('input', function() {
                const esPrimerSlider = this.id === Object.keys(mezclaReal)[0]; // Verificar si es el primer slider
                const valorIntento = parseInt(this.value);
                const valorInicial = esPrimerSlider ? mezclaReal[this.id] : 0; // Valor inicial solo para el primer slider
                const sumaActual = calcularSumaActualSin(this.id);
                const sumaConIntento = sumaActual + valorIntento;

                if (esPrimerSlider && valorIntento < valorInicial) {
                    // Si es el primer slider y el valor es menor que el inicial, restablecer al valor inicial
                    this.value = valorInicial;
                    document.getElementById(this.id + 'Value').innerText = valorInicial + '%';
                } else if (sumaConIntento <= 100) {
                    // Ajuste para asegurarse de que la suma total no supere el 100%
                    document.getElementById(this.id + 'Value').innerText = valorIntento + '%';
                } else {
                    // Ajustar el valor para que la suma total no exceda el 100%
                    this.value = 100 - sumaActual;
                    document.getElementById(this.id + 'Value').innerText = this.value + '%';
                }
                actualizarSumaTotal();
            });
        });


        function calcularSumaActualSin(excluirId) {
            let suma = 0;
            sliders.forEach(slider => {
                if (slider.id !== excluirId) {
                    suma += parseInt(slider.value);
                }
            });
            return suma;
        }

        function actualizarSumaTotal() {
            let nuevaSuma = 0;
            sliders.forEach(slider => {
                nuevaSuma += parseInt(slider.value);
            });
            sumaTotalElemento.innerText = nuevaSuma + '%';
        }


        inicializar();

        function inicializar() {
            actualizarSumaTotal();
            sliders.forEach(slider => {
                document.getElementById(slider.id + 'Value').innerText = slider.value + '%';
            });
        }

        window.calcular = function() {
            let puntuacionFinal = 99; // Máximo de acierto
            let sumaSliders = 0;
            Object.keys(mezclaReal).forEach(cepa => {
                const valorSlider = parseInt(document.getElementById(cepa).value);
                sumaSliders += valorSlider; // Sumar todos los valores de los sliders
                puntuacionFinal -= Math.abs(valorSlider - mezclaReal[cepa]);
            });

            // Aplicar corrección si la suma de los sliders es 100%
            if (sumaSliders >= 100) {
                puntuacionFinal = Math.min(puntuacionFinal, 98); // Asegurar que no supere 98 si la suma es 100%
            }

            const cepasNulas = Object.keys(mezclaReal).filter(cepa => mezclaReal[cepa] === 0);
            cepasNulas.forEach(cepa => {
                const valorSlider = parseInt(document.getElementById(cepa).value);
                if (sumaSliders < 100) { // Solo restar si la suma de sliders es menor a 100
                    puntuacionFinal -= valorSlider;
                }
            });

            puntuacionFinal = Math.max(puntuacionFinal, 0);
            let urlDestino;
            if (puntuacionFinal >= 90) {
                urlDestino = urlBase + "congrats-" + Math.floor(puntuacionFinal);
            } else {
                urlDestino = urlBase + "try-again";
            }

            window.location.href = urlDestino;
        };


    });
    </script>
     
     <!-- JavaScript para el efecto de relleno dinámico -->
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sliders = document.querySelectorAll('input[type="range"]');
            sliders.forEach(function(slider) {
                // Aplicar el relleno inicial basado en el valor actual del slider.
                updateSliderFill(slider);

                slider.addEventListener('input', function() {
                    // Actualizar el relleno cada vez que el valor del slider cambia.
                    updateSliderFill(this);
                });
            });

            

        });
        function updateSliderFill(slider) {
                const percentage = ((slider.value - slider.min) / (slider.max - slider.min)) * 100;
                const sliderColor = slider.getAttribute('data-color');
                slider.style.background = `linear-gradient(to right, ${sliderColor} ${percentage}%, #ccc ${percentage}%)`;
        }
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.cepa-checkbox');
            checkboxes.forEach(function(checkbox) {
                const label = document.querySelector('label[for="' + checkbox.id + '"]');
                const colorInactivo = label.style.getPropertyValue('--boton-bg-color-inactivo');
                
                // Aplicar el color inactivo con opacidad si el checkbox no está marcado
                if (!checkbox.checked) {
                    label.style.backgroundColor = colorInactivo;
                }
            });
        });

    </script>

    <?php
    return ob_get_clean(); // Devolver el contenido generado y limpiar el buffer
}

add_shortcode( 'adivina_la_mezcla', 'adivina_la_mezcla_shortcode' );
