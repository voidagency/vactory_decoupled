<?php

namespace Drupal\vactory_jenkins\Form;

use Drupal\vactory_jenkins\Trigger;
use Drupal\vactory_jenkins\DeployLogger;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Site\Settings;

/**
 * Class DeploymentForm.
 */
class DeploymentForm extends FormBase
{

  /**
   * Drupal\vactory_jenkins\Trigger.
   *
   * @var \Drupal\vactory_jenkins\Trigger
   */
  protected $buildHooksTrigger;

  /**
   * Drupal\vactory_jenkins\DeployLogger.
   *
   * @var \Drupal\vactory_jenkins\DeployLogger
   */
  protected $buildHooksDeploylogger;

  /**
   * Drupal\Core\Render\Renderer definition.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Drupal\Core\Datetime\DateFormatter definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs a new DeploymentForm object.
   */
  public function __construct(
    Trigger $build_hooks_trigger,
    DeployLogger $build_hooks_deploylogger,
    Renderer $renderer,
    DateFormatter $dateFormatter
  )
  {
    $this->buildHooksTrigger = $build_hooks_trigger;
    $this->buildHooksDeploylogger = $build_hooks_deploylogger;
    $this->renderer = $renderer;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * Create.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   *
   * @return \Drupal\vactory_jenkins\Form\DeploymentForm
   *   The deployment form.
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('vactory_jenkins.trigger'),
      $container->get('vactory_jenkins.deploylogger'),
      $container->get('renderer'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'deployment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $frontend_url = Settings::get('DEPLOY_FRONTEND_URL', FALSE);
    $webhook_url_webhook = Settings::get('DEPLOY_WEBHOOK_URL', FALSE);
    $webhook_url_build = Settings::get('DEPLOY_WEBHOOK_LAST_BUILD_URL', FALSE);
    $webhook_url_log = Settings::get('DEPLOY_WEBHOOK_LAST_BUILD_CONSOLE_URL', FALSE);

    if (!$webhook_url_webhook) {
      \Drupal::messenger()->addError(t("Deployment is unavailable. Make sure you've set DEPLOY_WEBHOOK_URL in settings.php file."));
      return $form;
    }


    // Build status.
    $lastBuildStatus = [
      'status' => ''
    ];
    if ($webhook_url_build) {
      $lastBuildStatus = $this->buildHooksTrigger->getLastBuildStatus($webhook_url_build);
    } else {
      \Drupal::messenger()->addWarning(t("Status is unavailable. Make sure you've set DEPLOY_WEBHOOK_LAST_BUILD_URL in settings.php file."));
    }

    // Build log.
    $lastBuildLog = '';
    if ($webhook_url_log) {
      $lastBuildLog = $this->buildHooksTrigger->getLastBuildLog($webhook_url_log);
    } else {
      \Drupal::messenger()->addWarning(t("Log is unavailable. Make sure you've set DEPLOY_WEBHOOK_LAST_BUILD_CONSOLE_URL in settings.php file."));
    }

    $form['display'] = [
      '#markup' => '<h2>' . $this->t('Frontend Environment') . '</h2>',
    ];

    if (!empty($lastBuildStatus)) {
      switch ($lastBuildStatus['status']) {
        case NULL:
          $statusLabel = '<span style="color:#0a6eb4"><b>IN PROGRESS</b></span>';
          break;
        case 'SUCCESS':
          $statusLabel = '<span style="color:#09b415"><b>SUCCESS</b></span>';
          break;
        case 'FAIL':
          $statusLabel = '<span style="color:#b43716"><b>FAILED</b></span>';
          break;
        default:
          $statusLabel = '<span style="color:#2e1634"><b>UNKNOWN</b></span>';
      }

      $form['status'] = [
        '#markup' => \Drupal\Core\Render\Markup::create('<h2>' . $this->t('Status') . '</h2><ul><li>' . $this->t('Name') . ': ' . $lastBuildStatus['name'] . '</li><li>' . $this->t('Status') . ': ' . $statusLabel . '</li></ul>'),
      ];
    }

    if ($frontend_url) {
      $form['environment_link'] = [
        '#markup' => '<h2>' . $this->t('Lien') . '</h2><p>' . Link::fromTextAndUrl($frontend_url, Url::fromUri($frontend_url, ['attributes' => ['target' => '_blank']]))
            ->toString() . '</p>',
      ];
    }


    // When was the last deployment?
    $last_deployment_timestamp = $this->buildHooksDeploylogger->getLastDeployTime();
    if ($last_deployment_timestamp) {
      $last_deployment_timestamp_formatted = $this->dateFormatter->format($last_deployment_timestamp, 'long');
      $form['last_deployment'] = [
        '#markup' => '<p>' . $this->t('Last deployment triggered on: <strong>@date</strong>', ['@date' => $last_deployment_timestamp_formatted]) . '</p>',
      ];
    }

    if (!empty($lastBuildLog)) {
      $form['last_deployment_log'] = [
        '#type' => 'textarea',
        '#cols' => '80',
        '#rows' => '20',
        '#title' => t('Log'),
        '#default_value' => $lastBuildLog,
      ];
    }

    $form['changelog'] = [
      '#type' => 'details',
      '#title' => $this->t('Changelog'),
      '#description' => $this->t("This is a summary of the changes since the previous deployment:") . '</p>',
      '#open' => TRUE,
    ];

    // Have we logged any changes since last deployment?
    if ($this->buildHooksDeploylogger->getNumberOfItemsSinceLastDeployment() > 0) {
      try {
        $form['changelog']['log'] = [
          '#markup' => $this->getChangelogView($last_deployment_timestamp),
        ];
      } catch (\Exception $e) {
        $this->messenger()
          ->addWarning($this->t('Could not render the view with the changelog. Check configuration.'));
      }

    } else {
      $form['changelog']['#description'] = '<p>' . $this->t('No changes recorded since the last deployment for this environment. If needed you can still trigger a deployment using this page.') . '</p>';
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Start a new deployment'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // Run a complete cache flush.
    drupal_flush_all_caches();
    // Trigger build.
    $this->buildHooksTrigger->triggerBuild();
  }

  /**
   * Use the included view to render the changelog.
   *
   * @param int $timestamp
   *   Timestamp argument to get the changelog starting from.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   *   The rendered results.
   *
   * @throws \Exception
   */
  private function getChangelogView($timestamp)
  {
    $changelog_view = Views::getView('vactory_jenkins_editing_log');
    $wids = $this->buildHooksDeploylogger->getLogItemsSinceTimestamp($timestamp);
    $arg = implode('+', $wids);
    $renderable_view = $changelog_view->buildRenderable('embed_1', [$arg]);
    return $this->renderer->render($renderable_view);
  }

}
