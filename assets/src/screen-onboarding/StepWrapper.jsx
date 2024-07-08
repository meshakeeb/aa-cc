export default function StepWrapper({ title, children }) {
	return (
		<div className="bg-white mt-4 mb-8 p-8 border-solid border-gray-200 rounded-sm">
			{title && <h1 className="!mt-0">{title}</h1>}
			{children}
		</div>
	);
}
