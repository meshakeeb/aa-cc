/**
 * External Dependencies
 */
import { wizard } from '@advancedAds/i18n';

/**
 * Internal Dependencies
 */
import { useWizard } from '@components/wizard';
import Divider from '@components/Divider';

export default function StepFooter({
	isEnabled,
	enableText,
	disableText,
	onNext,
}) {
	const { previousStep, nextStep } = useWizard();

	const handleNext = async () => {
		if (onNext) {
			onNext();
		}

		nextStep();
	};

	return (
		<>
			<Divider />
			<div className="flex items-center">
				<div>
					<button
						onClick={previousStep}
						className="button-link !text-base"
					>
						&larr; <span>{wizard.btnGoBack}</span>
					</button>
				</div>
				<div className="text-right flex-1">
					{isEnabled ? (
						<button
							className="button button-hero button-primary"
							onClick={handleNext}
						>
							{enableText}
						</button>
					) : (
						<button className="button button-hero" disabled={true}>
							{disableText}
						</button>
					)}
				</div>
			</div>
		</>
	);
}
