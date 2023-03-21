<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace statikbe\molliesubscriptions\actions;

use Craft;
use craft\base\ElementAction;
use craft\helpers\Json;

class ExportAllSubscriptionsAction extends ElementAction
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('app', 'Export all to csv');
    }

    public function getTriggerHtml(): ?string
    {
        $type = Json::encode(static::class);

        $js = <<<EOT
(function()
{
	var trigger = new Craft.ElementActionTrigger({
		handle: 'MollieSubscriptions_ExportCSV',
		batch: true,
		type: {$type},
		activate: function(\$selectedItems)
		{
		    var ids = [];
		    \$selectedItems.each(function() {
		        ids.push($(this).data("id"));
		    });
		    
			var form = $('<form method="post" target="_blank">' +
			'<input type="hidden" name="action" value="{action}" />' +
			'<input type="hidden" name="ids" value="' + ids.join(",") + '" />' +
			'<input type="hidden" name="{csrfName}" value="{csrfValue}" />' +
			'<input type="submit" value="Submit" />' +
			'</form>');
			
			form.appendTo('body');
			form.submit();
			form.remove();
		}
	});
})();
EOT;

        $js = str_replace(
            ['{csrfName}', '{csrfValue}', '{action}'],
            [
                Craft::$app->config->general->csrfTokenName,
                Craft::$app->request->getCsrfToken(),
                'mollie-subscriptions/subscriptions/export-all'
            ],
            $js
        );

        \Craft::$app->view->registerJs($js);
        return null;
    }

    public static function isDestructive(): bool
    {
        return false;
    }

}
