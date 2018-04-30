<?php

namespace Drupal\islandora_openseadragon\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\token\TreeBuilderInterface;

/**
 * Module administration form.
 */
class Admin extends ConfigFormBase {

  protected $treeBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(TreeBuilderInterface $tree_builder) {
    $this->treeBuilder = $tree_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('token.tree_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_openseadragon_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('islandora_openseadragon.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['islandora_openseadragon.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form_state->loadInclude('islandora', 'inc', 'includes/utilities');
    $form_state->loadInclude('islandora_openseadragon', 'inc', 'includes/utilities');
    $form_state->loadInclude('islandora_openseadragon', 'inc', 'includes/admin.form');

    $settings = islandora_openseadragon_get_settings(FALSE);
    $version = islandora_openseadragon_get_installed_version();
    $djatoka_url = $form_state->getValue('islandora_openseadragon_djatoka_url') ?
      $form_state->getValue('islandora_openseadragon_djatoka_url') :
      $this->config('islandora_openseadragon.settings')->get('islandora_openseadragon_djatoka_url');
    $iiif_url = $form_state->getValue('islandora_openseadragon_iiif_url') ?
      $form_state->getValue('islandora_openseadragon_iiif_url') :
      $this->config('islandora_openseadragon.settings')->get('islandora_openseadragon_iiif_url');
    $token_tree = [
      'islandora_openseadragon' => [
        'tokens' => $this->treeBuilder->buildTree('islandora_openseadragon', []),
      ],
    ];

    $form = [
      'islandora_openseadragon_tilesource' => [
        '#type' => 'select',
        '#title' => $this->t('Image Server'),
        '#description' => $this->t('Select the image server to use with OpenSeadragon'),
        '#default_value' => $this->config('islandora_openseadragon.settings')->get('islandora_openseadragon_tilesource'),
        '#options' => [
          'djatoka' => $this->t('Adore-Djatoka Image Server'),
          'iiif' => $this->t('IIIF Image Server'),
        ],
      ],
      'islandora_openseadragon_fit_to_aspect_ratio' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Constrain image to viewport'),
        '#default_value' => $this->config('islandora_openseadragon.settings')->get('islandora_openseadragon_fit_to_aspect_ratio'),
        '#description' => $this->t('On the initial page load, the entire image will be visible in the viewport.'),
      ],
      'djatoka' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Adore Djatoka Image Server Settings'),
        '#description' => $this->t('<p>Settings for Adore-Djatoka Image Server</p>'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_openseadragon_tilesource"]' => ['value' => 'djatoka'],
          ],
        ],
        'islandora_openseadragon_djatoka_url' => [
          '#type' => 'textfield',
          '#prefix' => '<div id="islandora-openseadragon-djatoka-path-wrapper">',
          '#suffix' => '</div>',
          '#title' => $this->t('Adore-Djatoka Image Server Base URL'),
          '#title_display' => 'invisible',
          '#default_value' => $djatoka_url,
          '#description' => $this->t('The location of the Adore-Djatoka server OpenURL resolver. <br/>')
          . islandora_openseadragon_admin_form_djatoka_access_message($djatoka_url),
          '#ajax' => [
            'callback' => 'islandora_openseadragon_admin_ajax_djatoka_url',
            'wrapper' => 'islandora-openseadragon-djatoka-path-wrapper',
            'disable-refocus' => TRUE,
          ],
        ],
      ],
      'iiif' => [
        '#type' => 'fieldset',
        '#title' => $this->t('IIIF Image Server Settings'),
        '#description' => $this->t('Settings for IIIF Image Server'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_openseadragon_tilesource"]' => ['value' => 'iiif'],
          ],
        ],
        'islandora_openseadragon_iiif_url' => [
          '#prefix' => '<div id="islandora-openseadragon-iiif-path-wrapper">',
          '#suffix' => '</div>',
          '#type' => 'textfield',
          '#title' => $this->t('IIIF Image Server Base URL'),
          '#default_value' => $iiif_url,
          '#description' => $this->t('The location of the IIIF Image Server.'),
        ],
        'islandora_openseadragon_iiif_token_header' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Add token as header'),
          '#default_value' => $this->config('islandora_openseadragon.settings')->get('islandora_openseadragon_iiif_token_header'),
          '#description' => $this->t('Instead of sending the token as a query parameter, it will be sent in the X-ISLANDORA-TOKEN header.'),
        ],
        'islandora_openseadragon_iiif_identifier' => [
          '#type' => 'textfield',
          '#title' => $this->t('IIIF Identifier'),
          '#default_value' => $this->config('islandora_openseadragon.settings')->get('islandora_openseadragon_iiif_identifier'),
          '#element_validate' => ['token_element_validate'],
          '#token_types' => ['islandora_openseadragon'],
        ],
        'islandora_openseadragon_iiif_token_tree' => [
          '#type' => 'fieldset',
          '#title' => $this->t('Replacement patterns'),
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
          'tokens' => [
            '#type' => 'token_tree_table',
            '#token_tree' => $token_tree,
          ],
          '#description' => [
            '#theme' => 'token_tree',
            '#token_types' => ['islandora_openseadragon'],
            '#global_types' => FALSE,
          ],
        ],
      ],
      'tilesource' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Open Seadragon Tile Source settings'),
        '#description' => $this->t('<p>These settings will apply to all tile sources globally. See the <a href="https://openseadragon.github.io/docs/OpenSeadragon.TileSource.html#TileSource">documentation</a> for more details.</p>'),
        'islandora_openseadragon_tile_size' => [
          '#type' => 'number',
          '#title' => $this->t('Tile Size'),
          '#default_value' => $this->config('islandora_openseadragon.settings')->get('islandora_openseadragon_tile_size'),
          '#size' => 10,
          '#description' => $this->t('The size of the tiles to assumed to make up each pyramid layer in pixels. Tile size determines the point at which the image pyramid must be divided into a matrix of smaller images.'),
        ],
        'islandora_openseadragon_tile_overlap' => [
          '#type' => 'number',
          '#title' => $this->t('Tile Overlap'),
          '#default_value' => $this->config('islandora_openseadragon.settings')->get('islandora_openseadragon_tile_overlap'),
          '#size' => 10,
          '#description' => $this->t('The number of pixels each tile is expected to overlap touching tiles.'),
        ],
      ],
      'openseadragon' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Open Seadragon Viewer settings'),
        '#description' => $this->t('<p>Settings for OpenSeadragon %version, see the <a href="https://openseadragon.github.io/docs/OpenSeadragon.html#.Options">documentation</a> for more details.</p>', [
          '%version' => $version,
        ]),
        'islandora_openseadragon_settings' => [
          '#type' => 'container',
          '#tree' => TRUE,
          // We don't provide "id" as configurable to users.
          // We don't provide "element" as configurable to users.
          // We don't provide "tileSources" as configurable to users.
          'tabIndex' => [
            '#type' => 'number',
            '#title' => $this->t('Tab Index'),
            '#default_value' => $settings['tabIndex'],
            '#size' => 10,
            '#description' => $this->t('Tabbing order index to assign to the viewer element. Positive values are selected in increasing order. When tabIndex is 0 source order is used. A negative value omits the viewer from the tabbing order.'),
          ],
          'debugMode' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Debug mode'),
            '#default_value' => $settings['debugMode'],
            '#description' => $this->t('Toggles whether messages should be logged and fail-fast behavior should be provided.'),
          ],
          'debugGridColor' => [
            '#type' => 'textfield',
            '#title' => $this->t('Debug Grid Color'),
            '#default_value' => $settings['debugGridColor'],
            '#description' => $this->t('Color of the grid in debug mode.'),
          ],
          'blendTime' => [
            '#type' => 'number',
            '#title' => $this->t('Blend time'),
            '#size' => 10,
            '#default_value' => $settings['blendTime'],
            '#description' => $this->t('Specifies the duration of animation as higher or lower level tiles are replacing the existing tile.'),
          ],
          'alwaysBlend' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Always blend'),
            '#default_value' => $settings['alwaysBlend'],
            '#description' => $this->t("Forces the tile to always blend. By default the tiles skip blending when the blendTime is surpassed and the current animation frame would not complete the blend."),
          ],
          'autoHideControls' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Auto-hide controls'),
            '#default_value' => $settings['autoHideControls'],
            '#description' => $this->t("If the user stops interacting with the viewport, fade the navigation controls. Useful for presentation since the controls are by default floated on top of the image the user is viewing."),
          ],
          'immediateRender' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Immediate render'),
            '#default_value' => $settings['immediateRender'],
            '#description' => $this->t('Render the best closest level first, ignoring the lowering levels which provide the effect of very blurry to sharp. It is recommended to change setting to true for mobile devices.'),
          ],
          'defaultZoomLevel' => [
            '#type' => 'number',
            '#title' => $this->t('Default zoom level'),
            '#size' => 10,
            '#default_value' => $settings['defaultZoomLevel'],
            '#description' => $this->t('Zoom level to use when image is first opened or the home button is clicked. If 0, adjusts to fit viewer.'),
          ],
          'opacity' => [
            '#type' => 'number',
            '#title' => $this->t('Opacity'),
            '#size' => 10,
            '#default_value' => $settings['opacity'],
            '#description' => $this->t('Default opacity of the tiled images (1=opaque, 0=transparent)'),
          ],
          'compositeOperation' => [
            '#type' => 'select',
            '#title' => $this->t('Composite Operation'),
            '#description' => $this->t('Select the image server to use with OpenSeadragon'),
            '#default_value' => $settings['compositeOperation'],
            '#options' => array_combine([
              NULL,
              'source-over',
              'source-atop',
              'source-in',
              'source-out',
              'destination-over',
              'destination-atop',
              'destination-in',
              'destination-out',
              'lighter',
              'copy',
              'xor',
            ], [
              '',
              'source-over',
              'source-atop',
              'source-in',
              'source-out',
              'destination-over',
              'destination-atop',
              'destination-in',
              'destination-out',
              'lighter',
              'copy',
              'xor',
            ]),
          ],
          'placeholderFillStyle' => [
            '#type' => 'textfield',
            '#title' => $this->t('Placeholder Fill Style'),
            '#default_value' => $settings['placeholderFillStyle'],
            '#description' => $this->t('Draws a colored rectangle behind the tile if it is not loaded yet. You can pass a CSS color value like "#FF8800".'),
          ],
          'degrees' => [
            '#type' => 'number',
            '#title' => $this->t('Initial Rotation'),
            '#size' => 10,
            '#default_value' => $settings['degrees'],
            '#description' => $this->t('Initial rotation in degrees.'),
          ],
          'minZoomLevel' => [
            '#type' => 'number',
            '#title' => $this->t('Minimum Zoom Level'),
            '#size' => 10,
            '#default_value' => $settings['minZoomLevel'],
            '#description' => $this->t('Minimum Zoom Level (integer).'),
          ],
          'maxZoomLevel' => [
            '#type' => 'number',
            '#title' => $this->t('Maximum Zoom Level'),
            '#size' => 10,
            '#default_value' => $settings['maxZoomLevel'],
            '#description' => $this->t('Maximum Zoom Level (integer).'),
          ],
          'homeFillsViewer' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Home Button Fills Viewer'),
            '#default_value' => $settings['homeFillsViewer'],
            '#description' => $this->t('Make the "home" button fill the viewer and clip the image, instead of fitting the image to the viewer and letterboxing.'),
          ],
          'panHorizontal' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Pan horizontal'),
            '#default_value' => $settings['panHorizontal'],
            '#description' => $this->t('Allow horizontal pan.'),
          ],
          'panVertical' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Pan vertical'),
            '#default_value' => $settings['panVertical'],
            '#description' => $this->t('Allow vertical pan.'),
          ],
          'constrainDuringPan' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Constrain During Pan'),
            '#default_value' => $settings['constrainDuringPan'],
          ],
          'wrapHorizontal' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Wrap horizontal'),
            '#default_value' => $settings['wrapHorizontal'],
            '#description' => $this->t('Set to true to force the image to wrap horizontally within the viewport. Useful for maps or images representing the surface of a sphere or cylinder.'),
          ],
          'wrapVertical' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Wrap vertical'),
            '#default_value' => $settings['wrapVertical'],
            '#description' => $this->t('Set to true to force the image to wrap vertically within the viewport. Useful for maps or images representing the surface of a sphere or cylinder.'),
          ],
          'minZoomImageRatio' => [
            '#type' => 'textfield',
            '#element_validate' => ['::validateNumber'],
            '#title' => $this->t('Minimum zoom image ratio'),
            '#size' => 10,
            '#default_value' => $settings['minZoomImageRatio'],
            '#description' => $this->t('The minimum percentage ( expressed as a number between 0 and 1 ) of the viewport height or width at which the zoom out will be constrained. Setting it to 0, for example will allow you to zoom out infinity.'),
          ],
          'maxZoomPixelRatio' => [
            '#type' => 'textfield',
            '#element_validate' => ['::validateNumber'],
            '#title' => $this->t('Maximum zoom pixel ratio'),
            '#size' => 10,
            '#default_value' => $settings['maxZoomPixelRatio'],
            '#description' => $this->t('The maximum ratio to allow a zoom-in to affect the highest level pixel ratio. This can be set to Infinity to allow "infinite" zooming into the image though it is less effective visually if the HTML5 Canvas is not available on the viewing device.'),
          ],
          'smoothTileEdgesMinZoom' => [
            '#type' => 'textfield',
            '#element_validate' => ['::validateNumber'],
            '#title' => $this->t('Smooth Tile Edges Minimum Zoom'),
            '#size' => 10,
            '#default_value' => $settings['smoothTileEdgesMinZoom'],
            '#description' => $this->t('A zoom percentage ( where 1 is 100% ) of the highest resolution level. When zoomed in beyond this value alternative compositing will be used to smooth out the edges between tiles. This will have a performance impact. Can be set to Infinity to turn it off. Note: This setting is ignored on iOS devices due to a known bug (See <a href="https://github.com/openseadragon/openseadragon/issues/952">https://github.com/openseadragon/openseadragon/issues/952</a>).'),
          ],
          // We don't provide "iOSDevice" as configurable to users.
          'autoResize' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Auto Resize'),
            '#default_value' => $settings['autoResize'],
            '#description' => $this->t('Set to false to prevent polling for viewer size changes. Useful for providing custom resize behavior.'),
          ],
          'preserveImageSizeOnResize' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Preserve Image Size On Resize'),
            '#default_value' => $settings['preserveImageSizeOnResize'],
            '#description' => $this->t('Set to true to have the image size preserved when the viewer is re-sized. This requires Auto Resize to be enabled (default).'),
          ],
          'minScrollDeltaTime' => [
            '#type' => 'number',
            '#title' => $this->t('Minimum Scroll Delta Time'),
            '#size' => 10,
            '#default_value' => $settings['minScrollDeltaTime'],
            '#description' => $this->t('Number of milliseconds between canvas-scroll events. This value helps normalize the rate of canvas-scroll events between different devices, causing the faster devices to slow down enough to make the zoom control more manageable.'),
          ],
          'pixelsPerWheelLine' => [
            '#type' => 'number',
            '#title' => $this->t('Pixels Per Wheel Line'),
            '#size' => 10,
            '#default_value' => $settings['pixelsPerWheelLine'],
            '#description' => $this->t('For pixel-resolution scrolling devices, the number of pixels equal to one scroll line.'),
          ],
          'visibilityRatio' => [
            '#type' => 'textfield',
            '#element_validate' => ['::validateNumber'],
            '#title' => $this->t('Visibility ratio'),
            '#size' => 10,
            '#default_value' => $settings['visibilityRatio'],
            '#description' => $this->t("The percentage ( as a number from 0 to 1 ) of the source image which must be kept within the viewport. If the image is dragged beyond that limit, it will 'bounce' back until the minimum visibility ratio is achieved. Setting this to 0 and wrapHorizontal ( or wrapVertical ) to true will provide the effect of an infinitely scrolling viewport."),
          ],
          'imageLoaderLimit' => [
            '#type' => 'number',
            '#title' => $this->t('Image loader limit'),
            '#size' => 10,
            '#default_value' => $settings['imageLoaderLimit'],
            '#description' => $this->t('The maximum number of image requests to make concurrently. By default it is set to 0 allowing the browser to make the maximum number of image requests in parallel as allowed by the browsers policy.'),
          ],
          'clickTimeThreshold' => [
            '#type' => 'number',
            '#title' => $this->t('Click time threshold'),
            '#size' => 10,
            '#default_value' => $settings['clickTimeThreshold'],
            '#description' => $this->t('The number of milliseconds within which a pointer down-up event combination will be treated as a click gesture.'),
          ],
          'clickDistThreshold' => [
            '#type' => 'number',
            '#title' => $this->t('Click distance threshold'),
            '#size' => 10,
            '#default_value' => $settings['clickDistThreshold'],
            '#description' => $this->t('The maximum distance allowed between a pointer down event and a pointer up event to be treated as a click gesture.'),
          ],
          'dblClickTimeThreshold' => [
            '#type' => 'number',
            '#title' => $this->t('Double click distance threshold'),
            '#size' => 10,
            '#default_value' => $settings['dblClickTimeThreshold'],
            '#description' => $this->t('The number of milliseconds within which two pointer down-up event combinations will be treated as a double-click gesture.'),
          ],
          'dblClickDistThreshold' => [
            '#type' => 'number',
            '#title' => $this->t('Double click distance threshold'),
            '#size' => 10,
            '#default_value' => $settings['dblClickDistThreshold'],
            '#description' => $this->t('The maximum distance allowed between two pointer click events to be treated as a double-click gesture.'),
          ],
          'springStiffness' => [
            '#type' => 'textfield',
            '#element_validate' => ['::validateNumber'],
            '#title' => $this->t('Spring stiffness'),
            '#size' => 10,
            '#default_value' => $settings['springStiffness'],
            '#description' => $this->t('Determines how sharply the springs used for animations move.'),
          ],
          'animationTime' => [
            '#type' => 'textfield',
            '#element_validate' => ['::validateNumber'],
            '#title' => $this->t('Animation time'),
            '#size' => 10,
            '#default_value' => $settings['animationTime'],
            '#description' => $this->t('Specifies the animation duration per each OpenSeadragon.Spring which occur when the image is dragged or zoomed.'),
          ],
          'gestureSettingsMouse' => [
            '#type' => 'fieldset',
            '#title' => $this->t('Mouse Pointer Gesture Settings'),
            '#collapsible' => TRUE,
            '#collapsed' => TRUE,
            '#description' => $this->t('<p>Settings for gestures generated by a mouse pointer device. (See <a href="https://openseadragon.github.io/docs/OpenSeadragon.html#.GestureSettings">OpenSeadragon.GestureSettings</a>)</p>'),
            'scrollToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Scroll To Zoom'),
              '#default_value' => $settings['gestureSettingsMouse']['scrollToZoom'],
              '#description' => $this->t('Zoom on scroll gesture.'),
            ],
            'clickToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Click To Zoom'),
              '#default_value' => $settings['gestureSettingsMouse']['clickToZoom'],
              '#description' => $this->t('Zoom on click gesture.'),
            ],
            'dblClickToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Double Click To Zoom'),
              '#default_value' => $settings['gestureSettingsMouse']['dblClickToZoom'],
              '#description' => $this->t('Zoom on double-click gesture. Note: If set to true then clickToZoom should be set to false to prevent multiple zooms.'),
            ],
            'pinchToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Pinch To Zoom'),
              '#default_value' => $settings['gestureSettingsMouse']['pinchToZoom'],
              '#description' => $this->t('Zoom on pinch gesture.'),
            ],
            'flickEnabled' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Flick Gesture'),
              '#default_value' => $settings['gestureSettingsMouse']['flickEnabled'],
              '#description' => $this->t('Enable flick gesture.'),
            ],
            'flickMinSpeed' => [
              '#type' => 'number',
              '#title' => $this->t('Flick Minimum Speed'),
              '#size' => 10,
              '#default_value' => $settings['gestureSettingsMouse']['flickMinSpeed'],
              '#description' => $this->t('If flickEnabled is true, the minimum speed to initiate a flick gesture (pixels-per-second).'),
            ],
            'flickMomentum' => [
              '#type' => 'textfield',
              '#element_validate' => ['::validateNumber'],
              '#title' => $this->t('Flick Momentum'),
              '#size' => 10,
              '#default_value' => $settings['gestureSettingsMouse']['flickMomentum'],
              '#description' => $this->t('If flickEnabled is true, the momentum factor for the flick gesture.'),
            ],
            'pinchRotate' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Pinch Rotate'),
              '#default_value' => $settings['gestureSettingsMouse']['pinchRotate'],
              '#description' => $this->t('If pinchRotate is true, the user will have the ability to rotate the image using their fingers.'),
            ],
          ],
          'gestureSettingsTouch' => [
            '#type' => 'fieldset',
            '#title' => $this->t('Touch Pointer Gesture Settings'),
            '#collapsible' => TRUE,
            '#collapsed' => TRUE,
            '#description' => $this->t('<p>Settings for gestures generated by a touch pointer device. (See <a href="https://openseadragon.github.io/docs/OpenSeadragon.html#.GestureSettings">OpenSeadragon.GestureSettings</a>)</p>'),
            'scrollToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Scroll To Zoom'),
              '#default_value' => $settings['gestureSettingsTouch']['scrollToZoom'],
              '#description' => $this->t('Zoom on scroll gesture.'),
            ],
            'clickToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Click To Zoom'),
              '#default_value' => $settings['gestureSettingsTouch']['clickToZoom'],
              '#description' => $this->t('Zoom on click gesture.'),
            ],
            'dblClickToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Double Click To Zoom'),
              '#default_value' => $settings['gestureSettingsTouch']['dblClickToZoom'],
              '#description' => $this->t('Zoom on double-click gesture. Note: If set to true then clickToZoom should be set to false to prevent multiple zooms.'),
            ],
            'pinchToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Pinch To Zoom'),
              '#default_value' => $settings['gestureSettingsTouch']['pinchToZoom'],
              '#description' => $this->t('Zoom on pinch gesture.'),
            ],
            'flickEnabled' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Flick Gesture'),
              '#default_value' => $settings['gestureSettingsTouch']['flickEnabled'],
              '#description' => $this->t('Enable flick gesture.'),
            ],
            'flickMinSpeed' => [
              '#type' => 'number',
              '#title' => $this->t('Flick Minimum Speed'),
              '#size' => 10,
              '#default_value' => $settings['gestureSettingsTouch']['flickMinSpeed'],
              '#description' => $this->t('If flickEnabled is true, the minimum speed to initiate a flick gesture (pixels-per-second).'),
            ],
            'flickMomentum' => [
              '#type' => 'textfield',
              '#element_validate' => ['::validateNumber'],
              '#title' => $this->t('Flick Momentum'),
              '#size' => 10,
              '#default_value' => $settings['gestureSettingsTouch']['flickMomentum'],
              '#description' => $this->t('If flickEnabled is true, the momentum factor for the flick gesture.'),
            ],
            'pinchRotate' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Pinch Rotate'),
              '#default_value' => $settings['gestureSettingsMouse']['pinchRotate'],
              '#description' => $this->t('If pinchRotate is true, the user will have the ability to rotate the image using their fingers.'),
            ],
          ],
          'gestureSettingsPen' => [
            '#type' => 'fieldset',
            '#title' => $this->t('Pen Pointer Gesture Settings'),
            '#collapsible' => TRUE,
            '#collapsed' => TRUE,
            '#description' => $this->t('<p>Settings for gestures generated by a pen pointer device. (See <a href="https://openseadragon.github.io/docs/OpenSeadragon.html#.GestureSettings">OpenSeadragon.GestureSettings</a>)</p>'),
            'scrollToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Scroll To Zoom'),
              '#default_value' => $settings['gestureSettingsPen']['scrollToZoom'],
              '#description' => $this->t('Zoom on scroll gesture.'),
            ],
            'clickToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Click To Zoom'),
              '#default_value' => $settings['gestureSettingsPen']['clickToZoom'],
              '#description' => $this->t('Zoom on click gesture.'),
            ],
            'dblClickToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Double Click To Zoom'),
              '#default_value' => $settings['gestureSettingsPen']['dblClickToZoom'],
              '#description' => $this->t('Zoom on double-click gesture. Note: If set to true then clickToZoom should be set to false to prevent multiple zooms.'),
            ],
            'pinchToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Pinch To Zoom'),
              '#default_value' => $settings['gestureSettingsPen']['pinchToZoom'],
              '#description' => $this->t('Zoom on pinch gesture.'),
            ],
            'flickEnabled' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Flick Gesture'),
              '#default_value' => $settings['gestureSettingsPen']['flickEnabled'],
              '#description' => $this->t('Enable flick gesture.'),
            ],
            'flickMinSpeed' => [
              '#type' => 'number',
              '#title' => $this->t('Flick Minimum Speed'),
              '#size' => 10,
              '#default_value' => $settings['gestureSettingsPen']['flickMinSpeed'],
              '#description' => $this->t('If flickEnabled is true, the minimum speed to initiate a flick gesture (pixels-per-second).'),
            ],
            'flickMomentum' => [
              '#type' => 'textfield',
              '#element_validate' => ['::validateNumber'],
              '#title' => $this->t('Flick Momentum'),
              '#size' => 10,
              '#default_value' => $settings['gestureSettingsPen']['flickMomentum'],
              '#description' => $this->t('If flickEnabled is true, the momentum factor for the flick gesture.'),
            ],
            'pinchRotate' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Pinch Rotate'),
              '#default_value' => $settings['gestureSettingsMouse']['pinchRotate'],
              '#description' => $this->t('If pinchRotate is true, the user will have the ability to rotate the image using their fingers.'),
            ],
          ],
          'gestureSettingsUnknown' => [
            '#type' => 'fieldset',
            '#title' => $this->t('Unknown Pointer Gesture Settings'),
            '#collapsible' => TRUE,
            '#collapsed' => TRUE,
            '#description' => $this->t('<p>Settings for gestures generated by a unknown pointer device. (See <a href="https://openseadragon.github.io/docs/OpenSeadragon.html#.GestureSettings">OpenSeadragon.GestureSettings</a>)</p>'),
            'scrollToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Scroll To Zoom'),
              '#default_value' => $settings['gestureSettingsUnknown']['scrollToZoom'],
              '#description' => $this->t('Zoom on scroll gesture.'),
            ],
            'clickToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Click To Zoom'),
              '#default_value' => $settings['gestureSettingsUnknown']['clickToZoom'],
              '#description' => $this->t('Zoom on click gesture.'),
            ],
            'dblClickToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Double Click To Zoom'),
              '#default_value' => $settings['gestureSettingsUnknown']['dblClickToZoom'],
              '#description' => $this->t('Zoom on double-click gesture. Note: If set to true then clickToZoom should be set to false to prevent multiple zooms.'),
            ],
            'pinchToZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Pinch To Zoom'),
              '#default_value' => $settings['gestureSettingsUnknown']['pinchToZoom'],
              '#description' => $this->t('Zoom on pinch gesture.'),
            ],
            'flickEnabled' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Flick Gesture'),
              '#default_value' => $settings['gestureSettingsUnknown']['flickEnabled'],
              '#description' => $this->t('Enable flick gesture.'),
            ],
            'flickMinSpeed' => [
              '#type' => 'number',
              '#title' => $this->t('Flick Minimum Speed'),
              '#size' => 10,
              '#default_value' => $settings['gestureSettingsUnknown']['flickMinSpeed'],
              '#description' => $this->t('If flickEnabled is true, the minimum speed to initiate a flick gesture (pixels-per-second).'),
            ],
            'flickMomentum' => [
              '#type' => 'textfield',
              '#element_validate' => ['::validateNumber'],
              '#title' => $this->t('Flick Momentum'),
              '#size' => 10,
              '#default_value' => $settings['gestureSettingsPen']['flickMomentum'],
              '#description' => $this->t('If flickEnabled is true, the momentum factor for the flick gesture.'),
            ],
            'pinchRotate' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Pinch Rotate'),
              '#default_value' => $settings['gestureSettingsMouse']['pinchRotate'],
              '#description' => $this->t('If pinchRotate is true, the user will have the ability to rotate the image using their fingers.'),
            ],
          ],
          'zoomPerClick' => [
            '#type' => 'number',
            '#title' => $this->t('Zoom per click'),
            '#size' => 10,
            '#default_value' => $settings['zoomPerClick'],
            '#description' => $this->t('The "zoom distance" per mouse click or touch tap. Note: Setting this to 1.0 effectively disables the click-to-zoom feature (also see gestureSettings[Mouse|Touch|Pen].clickToZoom/dblClickToZoom).'),
          ],
          'zoomPerScroll' => [
            '#type' => 'textfield',
            '#element_validate' => ['::validateNumber'],
            '#title' => $this->t('Zoom per scroll'),
            '#size' => 10,
            '#default_value' => $settings['zoomPerScroll'],
            '#description' => $this->t('The "zoom distance" per mouse scroll or touch pinch. Note: Setting this to 1.0 effectively disables the mouse-wheel zoom feature (also see gestureSettings[Mouse|Touch|Pen].scrollToZoom}).'),
          ],
          'zoomPerSecond' => [
            '#type' => 'number',
            '#title' => $this->t('Zoom per second'),
            '#size' => 10,
            '#default_value' => $settings['zoomPerSecond'],
            '#description' => $this->t('The number of seconds to animate a single zoom event over.'),
          ],
          'navigatorOptions' => [
            '#type' => 'fieldset',
            '#title' => $this->t('Navigator options'),
            'showNavigator' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Show Navigator'),
              '#default_value' => $settings['showNavigator'],
              '#description' => $this->t('Set to true to make the navigator minimap appear.'),
            ],
            'navigatorContainer' => [
              '#type' => 'container',
              '#states' => [
                'visible' => [
                  ':input[name="islandora_openseadragon_settings[navigatorOptions][showNavigator]"]' => ['checked' => TRUE],
                ],
              ],
              // We don't provide "navigatorId" as configurable to users.
              'navigatorPosition' => [
                '#type' => 'select',
                '#title' => $this->t('Navigator Position'),
                '#description' => $this->t('If "ABSOLUTE" is specified, then navigator[Top|Left|Height|Width] determines the size and position of the navigator minimap in the viewer, and navigatorSizeRatio and navigatorMaintainSizeRatio are ignored. For "TOP_LEFT", "TOP_RIGHT", "BOTTOM_LEFT", and "BOTTOM_RIGHT", the navigatorSizeRatio or navigator[Height|Width] values determine the size of the navigator minimap.'),
                '#default_value' => $settings['navigatorPosition'],
                '#options' => array_combine([
                  'TOP_RIGHT',
                  'TOP_LEFT',
                  'BOTTOM_LEFT',
                  'BOTTOM_RIGHT',
                  'ABSOLUTE',
                ], [
                  'TOP_RIGHT',
                  'TOP_LEFT',
                  'BOTTOM_LEFT',
                  'BOTTOM_RIGHT',
                  'ABSOLUTE',
                ]),
              ],
              'navigatorSizeRatio' => [
                '#type' => 'textfield',
                '#element_validate' => ['::validateNumber'],
                '#title' => $this->t('Navigator Size Ratio'),
                '#size' => 10,
                '#default_value' => $settings['navigatorSizeRatio'],
                '#description' => $this->t('Ratio of navigator size to viewer size. Ignored if navigator[Height|Width] are specified.'),
              ],
              'navigatorMaintainSizeRatio' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Navigator Maintain Size Ration'),
                '#default_value' => $settings['navigatorMaintainSizeRatio'],
                '#description' => $this->t('If true, the navigator minimap is resized (using navigatorSizeRatio) when the viewer size changes.'),
              ],
              'navigatorTop' => [
                '#type' => 'number',
                '#title' => $this->t('Navigator Top Position'),
                '#size' => 10,
                '#default_value' => $settings['navigatorTop'],
                '#description' => $this->t('Specifies the location of the navigator minimap (see Navigator Position).'),
              ],
              'navigatorLeft' => [
                '#type' => 'number',
                '#title' => $this->t('Navigator Left Position'),
                '#size' => 10,
                '#default_value' => $settings['navigatorLeft'],
                '#description' => $this->t('Specifies the location of the navigator minimap (see Navigator Position).'),
              ],
              'navigatorHeight' => [
                '#type' => 'number',
                '#title' => $this->t('Navigator Height'),
                '#size' => 10,
                '#default_value' => $settings['navigatorHeight'],
                '#description' => $this->t('Specifies the size of the navigator minimap (see Navigator Position). If specified, Navigator Size Ratio and Navigator Maintain Size Ratio are ignored.'),
              ],
              'navigatorWidth' => [
                '#type' => 'number',
                '#title' => $this->t('Navigator Width'),
                '#size' => 10,
                '#default_value' => $settings['navigatorWidth'],
                '#description' => $this->t('Specifies the size of the navigator minimap (see Navigator Position). If specified, Navigator Size Ratio and Navigator Maintain Size Ratio are ignored.'),
              ],
              'navigatorAutoResize' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Navigator Auto Resize'),
                '#default_value' => $settings['navigatorAutoResize'],
                '#description' => $this->t('Set to false to prevent polling for navigator size changes. Useful for providing custom resize behavior. Setting to false can also improve performance when the navigator is configured to a fixed size.'),
              ],
              'navigatorAutoFade' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Navigator Auto Fade'),
                '#default_value' => $settings['navigatorAutoFade'],
                '#description' => $this->t('If the user stops interacting with the viewport, fade the navigator minimap. Setting to false will make the navigator minimap always visible.'),
              ],
              'navigatorAutoFade' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Navigator Auto Fade'),
                '#default_value' => $settings['navigatorAutoFade'],
                '#description' => $this->t('If the user stops interacting with the viewport, fade the navigator minimap. Setting to false will make the navigator minimap always visible.'),
              ],
              'navigatorRotate' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Navigator Rotate'),
                '#default_value' => $settings['navigatorRotate'],
                '#description' => $this->t('If true, the navigator will be rotated together with the viewer.'),
              ],
            ],
          ],
          'controlsFadeDelay' => [
            '#type' => 'number',
            '#title' => $this->t('Controls Fade Delay'),
            '#size' => 10,
            '#default_value' => $settings['controlsFadeDelay'],
            '#description' => $this->t('The number of milliseconds to wait once the user has stopped interacting with the interface before begining to fade the controls. Assumes showNavigationControl and autoHideControls are both true.'),
          ],
          'controlsFadeLength' => [
            '#type' => 'number',
            '#title' => $this->t('Controls Fade Length'),
            '#size' => 10,
            '#default_value' => $settings['controlsFadeLength'],
            '#description' => $this->t('The number of milliseconds to animate the controls fading out.'),
          ],
          'controlsFadeDelay' => [
            '#type' => 'number',
            '#title' => $this->t('Controls Fade Delay'),
            '#size' => 10,
            '#default_value' => $settings['controlsFadeDelay'],
            '#description' => $this->t('The number of milliseconds to wait once the user has stopped interacting with the interface before begining to fade the controls. Assumes showNavigationControl and autoHideControls are both true.'),
          ],
          'maxImageCacheCount' => [
            '#type' => 'number',
            '#title' => $this->t('Controls Fade Delay'),
            '#size' => 10,
            '#default_value' => $settings['maxImageCacheCount'],
            '#description' => $this->t('The max number of images we should keep in memory (per drawer).'),
          ],
          'timeout' => [
            '#type' => 'number',
            '#title' => $this->t('timeout'),
            '#size' => 10,
            '#default_value' => $settings['timeout'],
          ],
          'useCanvas' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Use Canvas'),
            '#default_value' => $settings['useCanvas'],
            '#description' => $this->t('Set to false to not use an HTML canvas element for image rendering even if canvas is supported.'),
          ],
          'minPixelRatio' => [
            '#type' => 'textfield',
            '#element_validate' => ['::validateNumber'],
            '#title' => $this->t('Minimum Pixel Ratio'),
            '#size' => 10,
            '#default_value' => $settings['minPixelRatio'],
            '#description' => $this->t('The higher the minPixelRatio, the lower the quality of the image that is considered sufficient to stop rendering a given zoom level. For example, if you are targeting mobile devices with less bandwith you may try setting this to 1.5 or higher.'),
          ],
          'mouseNavEnabled' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable Mouse Navigation'),
            '#default_value' => $settings['mouseNavEnabled'],
            '#description' => $this->t('Is the user able to interact with the image via mouse or touch. Default interactions include dragging the image in a plane, and zooming in toward and away from the image.'),
          ],
          'navigationOptions' => [
            '#type' => 'fieldset',
            '#title' => $this->t('Navigation Controls'),
            'showNavigationControl' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Show Navigation Control'),
              '#default_value' => $settings['showNavigationControl'],
              '#description' => $this->t('Set to false to prevent the appearance of the default navigation controls. Note that if set to false, the customs buttons set by the options zoomInButton, zoomOutButton etc, are rendered inactive.'),
            ],
            'navigationContainer' => [
              '#type' => 'container',
              '#states' => [
                'visible' => [
                  ':input[name="islandora_openseadragon_settings[navigationOptions][showNavigationControl]"]' => ['checked' => TRUE],
                ],
              ],
              'navigationControlAnchor' => [
                '#type' => 'select',
                '#title' => $this->t('Navigation Control Anchor'),
                '#default_value' => $settings['navigationControlAnchor'],
                '#description' => $this->t('Placement of the default navigation controls. To set the placement of the sequence controls, see the sequenceControlAnchor option.'),
                '#options' => array_combine([
                  'TOP_RIGHT',
                  'TOP_LEFT',
                  'BOTTOM_LEFT',
                  'BOTTOM_RIGHT',
                  'ABSOLUTE',
                ], [
                  'TOP_RIGHT',
                  'TOP_LEFT',
                  'BOTTOM_LEFT',
                  'BOTTOM_RIGHT',
                  'ABSOLUTE',
                ]),
              ],
              'showZoomControl' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Show Zoom Control'),
                '#default_value' => $settings['showZoomControl'],
                '#description' => $this->t('If true then + and - buttons to zoom in and out are displayed. Note: OpenSeadragon.Options.showNavigationControl is overriding this setting when set to false.'),
              ],
              'showHomeControl' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Show Home Control'),
                '#default_value' => $settings['showHomeControl'],
                '#description' => $this->t('documentation'),
              ],
              'showFullPageControl' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Show Full Page Control'),
                '#default_value' => $settings['showFullPageControl'],
                '#description' => $this->t('If true then the rotate left/right controls will be displayed as part of the standard controls. This is also subject to the browser support for rotate (e.g., viewer.drawer.canRotate()). Note: OpenSeadragon.Options.showNavigationControl is overriding this setting when set to false.'),
              ],
              'showRotationControl' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Show Rotation Control'),
                '#default_value' => $settings['showRotationControl'],
                '#description' => $this->t('If sequenceMode is true, then provide buttons for navigating forward and backward through the images.'),
              ],
            ],
          ],
          'sequenceControlAnchor' => [
            '#type' => 'select',
            '#title' => $this->t('Sequence Control Anchor'),
            '#default_value' => $settings['sequenceControlAnchor'],
            '#description' => $this->t('Placement of the default sequence controls.'),
            '#options' => array_combine([
              'TOP_RIGHT',
              'TOP_LEFT',
              'BOTTOM_LEFT',
              'BOTTOM_RIGHT',
              'ABSOLUTE',
            ], [
              'TOP_RIGHT',
              'TOP_LEFT',
              'BOTTOM_LEFT',
              'BOTTOM_RIGHT',
              'ABSOLUTE',
            ]),
          ],
          'navPrevNextWrap' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Navigation Previous/Next Wrap'),
            '#default_value' => $settings['navPrevNextWrap'],
            '#description' => $this->t('If true then the "previous" button will wrap to the last image when viewing the first image and the "next" button will wrap to the first image when viewing the last image.'),
          ],
          // We don't provide "zoomInButton" as configurable to users.
          // We don't provide "zoomOutButton" as configurable to users.
          // We don't provide "homeButton" as configurable to users.
          // We don't provide "fullPageButton" as configurable to users.
          // We don't provide "rotateLeftButton" as configurable to users.
          // We don't provide "rotateRightButton" as configurable to users.
          // We don't provide "previousButton" as configurable to users.
          // We don't provide "nextButton" as configurable to users.
          'sequenceOptions' => [
            '#type' => 'fieldset',
            '#title' => 'Sequence Mode',
            'sequenceMode' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Sequence Mode'),
              '#default_value' => $settings['sequenceMode'],
              '#description' => $this->t('Set to true to have the viewer treat your tilesources as a sequence of images to be opened one at a time rather than all at once.'),
            ],
            'sequenceContainer' => [
              '#type' => 'container',
              '#states' => [
                'visible' => [
                  ':input[name="islandora_openseadragon_settings[sequenceOptions][sequenceMode]"]' => ['checked' => TRUE],
                ],
              ],
              // We don't provide "initialPage" as configurable to users.
              'preserveViewport' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Preserve View-port'),
                '#default_value' => $settings['preserveViewport'],
                '#description' => $this->t('If sequenceMode is true, then normally navigating through each image resets the viewport to "home" position. If preserveViewport is set to true, then the viewport position is preserved when navigating between images in the sequence.'),
              ],
              'preserveOverlays' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Preserve Overlays'),
                '#default_value' => $settings['preserveOverlays'],
                '#description' => $this->t('If sequenceMode is true, then normally navigating through each image resets the overlays. If preserveOverlays is set to true, then the overlays added with OpenSeadragon.Viewer#addOverlay are preserved when navigating between images in the sequence. Note: setting preserveOverlays overrides any overlays specified in the global "overlays" option for the Viewer. It\'s also not compatible with specifying per-tileSource overlays via the options, as those overlays will persist even after the tileSource is closed.'),
              ],
              'showReferenceStrip' => [
                '#type' => 'checkbox',
                '#title' => $this->t('Show Reference Strip'),
                '#default_value' => $settings['showReferenceStrip'],
                '#description' => $this->t('If sequenceMode is true, then display a scrolling strip of image thumbnails for navigating through the images.'),
              ],
              'referenceStripContainer' => [
                '#type' => 'container',
                '#states' => [
                  'visible' => [
                    ':input[name="islandora_openseadragon_settings[sequenceOptions][showReferenceStrip]"]' => ['checked' => TRUE],
                  ],
                ],
                'referenceStripScroll' => [
                  '#type' => 'select',
                  '#title' => $this->t('Reference Strip Scroll'),
                  '#default_value' => $settings['referenceStripScroll'],
                  '#description' => $this->t('Display the reference strip horizontally or vertically.'),
                  '#options' => array_combine([
                    'horizontal',
                    'vertical',
                  ], [
                    'horizontal',
                    'vertical',
                  ]),
                ],
                // We don't provide "referenceStripElement" as configurable
                // to users.
                'referenceStripHeight' => [
                  '#type' => 'number',
                  '#title' => $this->t('Reference Strip Height'),
                  '#size' => 10,
                  '#default_value' => $settings['referenceStripHeight'],
                  '#description' => $this->t('Height of the reference strip in pixels.'),
                ],
                'referenceStripWidth' => [
                  '#type' => 'number',
                  '#title' => $this->t('Reference Strip Width'),
                  '#size' => 10,
                  '#default_value' => $settings['referenceStripWidth'],
                  '#description' => $this->t('Width of the reference strip in pixels.'),
                ],
                'referenceStripPosition' => [
                  '#type' => 'textfield',
                  '#title' => $this->t('Reference Strip Position'),
                  '#default_value' => $settings['referenceStripPosition'],
                  '#description' => $this->t('The position of the reference strip.'),
                  '#options' => array_combine([
                    'TOP_RIGHT',
                    'TOP_LEFT',
                    'BOTTOM_LEFT',
                    'BOTTOM_RIGHT',
                  ], [
                    'TOP_RIGHT',
                    'TOP_LEFT',
                    'BOTTOM_LEFT',
                    'BOTTOM_RIGHT',
                  ]),
                ],
                'referenceStripSizeRatio' => [
                  '#type' => 'textfield',
                  '#element_validate' => ['::validateNumber'],
                  '#title' => $this->t('Reference Strip Size Ratio'),
                  '#size' => 10,
                  '#default_value' => $settings['referenceStripSizeRatio'],
                  '#description' => $this->t('Ratio of reference strip size to viewer size.'),
                ],
              ],
            ],
          ],
          'collectionModeFields' => [
            '#type' => 'fieldset',
            '#title' => $this->t('Collection Mode'),
            'collectionMode' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Enable Collection Mode'),
              '#default_value' => $settings['collectionMode'],
              '#description' => $this->t('Set to true to have the viewer arrange your TiledImages in a grid or line.'),
            ],
            'collectionModeContainer' => [
              '#type' => 'container',
              '#states' => [
                'visible' => [
                  ':input[name="islandora_openseadragon_settings[collectionModeFields][collectionMode]"]' => ['checked' => TRUE],
                ],
              ],
              'collectionRows' => [
                '#type' => 'number',
                '#title' => $this->t('Collection Rows'),
                '#size' => 10,
                '#default_value' => $settings['collectionRows'],
                '#description' => $this->t('If collectionMode is true, specifies how many rows the grid should have. Use 1 to make a line. If collectionLayout is "vertical", specifies how many columns instead.'),
              ],
              'collectionColumns' => [
                '#type' => 'number',
                '#title' => $this->t('Collection Columns'),
                '#size' => 10,
                '#default_value' => $settings['collectionColumns'],
                '#description' => $this->t('If collectionMode is true, specifies how many columns the grid should have. Use 1 to make a line. If collectionLayout is "vertical", specifies how many rows instead. Ignored if collectionRows is not set to a falsy value.'),
              ],
              'collectionLayout' => [
                '#type' => 'select',
                '#title' => $this->t('Collection Layout'),
                '#default_value' => $settings['collectionLayout'],
                '#description' => $this->t('If collectionMode is true, specifies whether to arrange vertically or horizontally.'),
                '#options' => array_combine([
                  'horizontal',
                  'vertical',
                ], [
                  'horizontal',
                  'vertical',
                ]),
              ],
              'collectionTileSize' => [
                '#type' => 'textfield',
                '#title' => $this->t('Collection Tile Size'),
                '#size' => 10,
                '#default_value' => $settings['collectionTileSize'],
                '#description' => $this->t('If collectionMode is true, specifies the size, in viewport coordinates, for each TiledImage to fit into. The TiledImage will be centered within a square of the specified size.'),
              ],
              'collectionTileMargin' => [
                '#type' => 'number',
                '#title' => $this->t('Collection Tile Margin'),
                '#size' => 10,
                '#default_value' => $settings['collectionTileMargin'],
                '#description' => $this->t('If collectionMode is true, specifies the margin, in viewport coordinates, between each TiledImage.'),
              ],
            ],
          ],
          // We don't provide "crossOriginPolicy" as configurable to users.
          // We don't provide "ajaxWithCredentials" as configurable to users.
        ],
      ],
      'actions' => [
        '#type' => 'actions',
        'reset' => [
          '#type' => 'submit',
          '#value' => $this->t('Reset to defaults'),
          '#weight' => 1,
          '#submit' => ['islandora_openseadragon_admin_submit_reset'],
        ],
      ],
      '#submit' => ['islandora_openseadragon_admin_submit_normalize'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue(['islandora_openseadragon_tilesource']) == 'djatoka') {
      $element = $form['djatoka']['islandora_openseadragon_djatoka_url'];
      islandora_openseadragon_djatoka_url_validate($element, $form_state, $form);
    }
  }

  /**
   * Validate numbers, number type fails on decimals.
   */
  public function validateNumber(array &$element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if ($value != '' && !is_numeric($value)) {
      $form_state->setError($element, $this->t('%name must be a number.', [
        '%name' => $element['#title'],
      ]));
    }
  }

}
